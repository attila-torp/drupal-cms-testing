(function (Drupal, once) {
  Drupal.behaviors.emulsifyPrimaryNav = {
    attach(context) {
      once('emulsify-primary-nav', '.js-emulsify-toggle', context).forEach((toggle) => {
        const targetSelector = toggle.getAttribute('data-emulsify-target');
        const target = targetSelector ? context.querySelector(targetSelector) : null;

        if (!target) {
          return;
        }

        toggle.addEventListener('click', () => {
          const expanded = toggle.getAttribute('aria-expanded') === 'true';
          toggle.setAttribute('aria-expanded', (!expanded).toString());
          target.classList.toggle('is-open');
        });
      });
    },
  };
})(Drupal, once);
