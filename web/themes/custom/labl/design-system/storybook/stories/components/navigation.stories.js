import menuTemplate from '@emulsify/02-molecules/navigation/menu/menu.twig';
import primaryNavTemplate from '@emulsify/02-molecules/navigation/primary-nav/primary-nav.twig';

const menuItems = [
  {
    title: 'Home',
    url: '/',
    in_active_trail: true,
  },
  {
    title: 'About',
    url: '/about',
  },
  {
    title: 'Services',
    url: '/services',
    below: [
      { title: 'Consulting', url: '/services/consulting' },
      { title: 'Implementation', url: '/services/implementation' },
    ],
  },
];

export default {
  title: 'Molecules/Navigation',
};

export const Menu = () => menuTemplate({ items: menuItems });

export const PrimaryNav = () =>
  primaryNavTemplate({
    content: menuTemplate({ items: menuItems }),
    label: 'Main navigation',
    toggle_label: 'Menu',
    menu_id: 'storybook-menu',
  });
