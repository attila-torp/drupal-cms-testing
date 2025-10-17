<?php

namespace Drupal\labl_blocks\Storybook;

use Drupal\Component\Serialization\Json;

/**
 * Discovers Storybook stories and converts their metadata.
 */
class StoryDiscovery {

  /**
   * Tracks machine names that have already been generated.
   */
  protected array $usedIds = [];

  /**
   * Relative path from the Drupal root to the Storybook organisms folder.
   */
  public const ORGANISMS_RELATIVE_PATH = 'themes/custom/labl/design-system/storybook/src/stories/organisms';

  /**
   * Discovers all stories within the organisms directory.
   */
  public function discover(): array {
    $root = $this->getOrganismsRoot();
    if (!is_dir($root)) {
      return [];
    }

    $stories = [];
    foreach ($this->storyFiles($root) as $filePath) {
      $definition = $this->buildStoryDefinition($filePath, $root);
      if (!empty($definition)) {
        $stories[$definition['id']] = $definition;
      }
    }

    ksort($stories);
    return $stories;
  }

  /**
   * Returns the absolute path to the organisms directory.
   */
  public function getOrganismsRoot(): string {
    return rtrim(DRUPAL_ROOT, "/\\") . "/" . self::ORGANISMS_RELATIVE_PATH;
  }

  /**
   * Iterates through story files.
   */
  protected function storyFiles(string $root): \Generator {
    $directoryIterator = new \RecursiveDirectoryIterator(
      $root,
      \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
    );
    $iterator = new \RecursiveIteratorIterator($directoryIterator);
    foreach ($iterator as $fileInfo) {
      if ($fileInfo instanceof \SplFileInfo && $fileInfo->isFile()) {
        $filename = $fileInfo->getFilename();
        if (substr($filename, -11) === ".stories.js") {
          yield $fileInfo->getPathname();
        }
      }
    }
  }

  /**
   * Builds an individual story definition.
   */
  protected function buildStoryDefinition(string $filePath, string $root): ?array {
    $content = @file_get_contents($filePath);
    if ($content === FALSE) {
      return NULL;
    }

    $argsLiteral = $this->parseSection($content, "args");
    $argTypesLiteral = $this->parseSection($content, "argTypes");

    $argsArray = $this->convertJsObjectToPhpArray($argsLiteral ?? "{}");
    $argTypesArray = $this->convertJsObjectToPhpArray($argTypesLiteral ?? "{}");

    if ($argsArray === NULL) {
      $argsArray = [];
    }
    if ($argTypesArray === NULL) {
      $argTypesArray = [];
    }

    $relative = trim(str_replace($root, '', dirname($filePath)), "/\\");
    if ($relative === '') {
      return NULL;
    }

    $segments = explode(DIRECTORY_SEPARATOR, $relative);
    $template = end($segments);
    $twigFilename = $this->findTwigFilename($root, $relative, $template);
    if ($twigFilename === NULL) {
      return NULL;
    }

    $baseId = $this->buildMachineName($template);
    $id = $baseId;
    if (isset($this->usedIds[$id])) {
      $id = $this->buildMachineName($relative);
    }
    $this->usedIds[$id] = TRUE;

    $fields = [];
    foreach ($argsArray as $key => $value) {
      $fields[$key] = $this->buildFieldDefinition($key, $value, $argTypesArray[$key] ?? []);
    }

    return [
      "id" => $id,
      "label" => $this->buildLabel($relative),
      "relative_path" => $relative,
      "template" => $template,
      "twig_path" => $relative . "/" . $twigFilename,
      "theme" => $id,
      "fields" => $fields,
      "path" => self::ORGANISMS_RELATIVE_PATH . "/" . $relative,
    ];
  }

  /**
   * Determines the Twig filename for the story's template directory.
   */
  protected function findTwigFilename(string $root, string $relative, string $template): ?string {
    $directory = rtrim($root, "/\\") . DIRECTORY_SEPARATOR . $relative;
    $candidates = [
      $template . '.twig',
      $template . '.html.twig',
    ];

    foreach ($candidates as $candidate) {
      if (is_file($directory . DIRECTORY_SEPARATOR . $candidate)) {
        return $candidate;
      }
    }

    // Fall back to the first Twig file within the directory, if any.
    foreach (glob($directory . DIRECTORY_SEPARATOR . '*.twig') ?: [] as $path) {
      if (is_file($path)) {
        return basename($path);
      }
    }

    return NULL;
  }

  /**
   * Builds a machine-readable identifier from a path segment.
   */
  protected function buildMachineName(string $value): string {
    $machine = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "_", $value));
    return trim($machine, "_");
  }

  /**
   * Builds a human-friendly label from the relative path.
   */
  protected function buildLabel(string $relative): string {
    $parts = array_map(static function (string $part): string {
      $part = str_replace(["-", "_"], " ", $part);
      return ucwords($part);
    }, explode("/", $relative));
    return implode(" - ", $parts);
  }

  /**
   * Builds field metadata for a story argument.
   */
  protected function buildFieldDefinition(string $key, $value, array $argType): array {
    $type = gettype($value);
    if ($type === "object") {
      $value = (array) $value;
      $type = "array";
    }

    $control = $this->extractControlType($argType);
    $options = $this->normalizeOptions($argType["options"] ?? NULL);

    $element = $this->determineElement($type, $control, $options, $value);

    return [
      "name" => $key,
      "default" => $value,
      "value_type" => $type,
      "control" => $control,
      "options" => $options,
      "element" => $element["element"],
      "multiple" => $element["multiple"],
      "store_json" => $element["store_json"],
    ];
  }

  /**
   * Normalizes Storybook control options into machine => label pairs.
   */
  protected function normalizeOptions($options): array {
    if (!is_array($options) || $options === []) {
      return [];
    }

    $normalized = [];

    // Sequential arrays are treated as simple lists of values.
    if (array_values($options) === $options) {
      foreach ($options as $option) {
        if (is_array($option)) {
          if (isset($option['value']) || array_key_exists('value', $option)) {
            $value = $option['value'];
            if (is_scalar($value) || $value === NULL) {
              $label = $option['label'] ?? $option['name'] ?? $option['title'] ?? $value;
              $normalized[(string) $value] = is_scalar($label) ? (string) $label : (string) $value;
            }
          }
          continue;
        }

        if (is_scalar($option)) {
          $normalized[(string) $option] = (string) $option;
        }
      }

      return $normalized;
    }

    foreach ($options as $key => $value) {
      if (is_array($value)) {
        if (isset($value['value']) || array_key_exists('value', $value)) {
          $option_value = $value['value'];
          if (is_scalar($option_value) || $option_value === NULL) {
            $label = $value['label'] ?? $value['name'] ?? $value['title'] ?? $option_value;
            $normalized[(string) $option_value] = is_scalar($label) ? (string) $label : (string) $option_value;
            continue;
          }
        }
        if (is_scalar($key)) {
          $normalized[(string) $key] = is_scalar($value) ? (string) $value : (string) $key;
        }
        continue;
      }

      if (!is_scalar($value)) {
        continue;
      }

      if (is_string($key) && !is_numeric($key)) {
        $stringKey = (string) $key;
        $stringValue = (string) $value;
        $keyLooksMachine = $stringKey === strtolower($stringKey);
        $valueLooksMachine = $stringValue === strtolower($stringValue);

        if (!$keyLooksMachine && $valueLooksMachine) {
          $normalized[$stringValue] = $stringKey;
        }
        elseif ($keyLooksMachine && !$valueLooksMachine) {
          $normalized[$stringKey] = $stringValue;
        }
        else {
          $normalized[$stringKey] = $stringValue;
        }
      }
      else {
        $normalized[(string) $value] = (string) $value;
      }
    }

    return $normalized;
  }

  protected function extractControlType(array $argType): string {
    if (!isset($argType["control"])) {
      return '';
    }
    $control = $argType["control"];
    if (is_array($control)) {
      return (string) ($control["type"] ?? '');
    }
    return (string) $control;
  }

  protected function determineElement(string $type, string $control, array $options, $value): array {
    if (!empty($options)) {
      $controlLower = strtolower($control);
      if (in_array($controlLower, ["inline-check", "check", "checkboxes"], TRUE)) {
        return [
          "element" => "checkboxes",
          "multiple" => TRUE,
          "store_json" => FALSE,
        ];
      }
      if ($controlLower === "multi-select" || $type === "array") {
        return [
          "element" => "select",
          "multiple" => TRUE,
          "store_json" => FALSE,
        ];
      }
      if ($controlLower === "radio" || $controlLower === "inline-radio") {
        return [
          "element" => "radios",
          "multiple" => FALSE,
          "store_json" => FALSE,
        ];
      }

      return [
        "element" => "select",
        "multiple" => FALSE,
        "store_json" => FALSE,
      ];
    }

    switch ($type) {
      case "boolean":
        return [
          "element" => "checkbox",
          "multiple" => FALSE,
          "store_json" => FALSE,
        ];

      case "integer":
      case "double":
        return [
          "element" => "number",
          "multiple" => FALSE,
          "store_json" => FALSE,
        ];

      case "array":
        return [
          "element" => "textarea",
          "multiple" => FALSE,
          "store_json" => TRUE,
        ];
    }

    if (is_string($value)) {
      $needs_textarea = strlen($value) > 120 || strpos($value, "\n") !== FALSE;
      return [
        "element" => $needs_textarea ? "textarea" : "textfield",
        "multiple" => FALSE,
        "store_json" => FALSE,
      ];
    }

    return [
      "element" => "textfield",
      "multiple" => FALSE,
      "store_json" => FALSE,
    ];
  }

  protected function parseSection(string $content, string $key): ?string {
    $pattern = '/' . preg_quote($key, '/') . '\s*(?::|=)\s*\{/' ;
    if (!preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
      return NULL;
    }
    $offset = $matches[0][1];
    $start = strpos($content, '{', $offset);
    if ($start === FALSE) {
      return NULL;
    }
    $depth = 0;
    $length = strlen($content);
    for ($i = $start; $i < $length; $i++) {
      $char = $content[$i];
      if ($char === '{') {
        $depth++;
      }
      elseif ($char === '}') {
        $depth--;
        if ($depth === 0) {
          return substr($content, $start, $i - $start + 1);
        }
      }
    }
    return NULL;
  }

  protected function convertJsObjectToPhpArray(string $objectLiteral): ?array {
    $json = $this->sanitizeJsObject($objectLiteral);
    if ($json === NULL) {
      return NULL;
    }

    try {
      $decoded = Json::decode($json);
    }
    catch (\Exception $e) {
      return NULL;
    }

    return is_array($decoded) ? $decoded : NULL;
  }

  protected function sanitizeJsObject(string $objectLiteral): ?string {
    $working = trim($objectLiteral);
    if ($working === '') {
      return NULL;
    }

    $working = preg_replace('/\/\*.*?\*\//s', '', $working);
    $working = preg_replace('/\/\/.*$/m', '', $working);

    $working = preg_replace('/([\{,]\s*)([A-Za-z0-9_]+)\s*:/', '\1"\2":', $working);

    $working = str_replace("'", '"', $working);

    $working = preg_replace('/,\s*([}\]])/', '$1', $working);

    $working = str_replace(['undefined'], 'null', $working);

    return $working;
  }

}
