/**
 * Island runtime.
 *
 * Finds every [data-ssbd-island] the server rendered, matches it to a
 * component, and mounts React onto exactly that element. Anything the
 * runtime does not recognise is left alone — a page always works without
 * this file, which is the whole point of the architecture.
 */

import { createRoot } from 'react-dom/client';
import ThemeToggle from './islands/ThemeToggle.jsx';
import FilterTabs from './islands/FilterTabs.jsx';
import Faq, { harvestFaqItems } from './islands/Faq.jsx';
import InquiryForm from './islands/InquiryForm.jsx';

/**
 * Read an island's props.
 *
 * @param {Element} el
 * @returns {object}
 */
function readProps(el) {
  const raw = el.getAttribute('data-ssbd-props');
  if (!raw) return {};

  try {
    return JSON.parse(raw);
  } catch (error) {
    return {};
  }
}

/**
 * Each entry returns { target, element } — target is the DOM node React
 * takes over, which is not always the island root.
 */
const islands = {
  'theme-toggle': (root) => ({ target: root, element: <ThemeToggle /> }),

  'filter-grid': (root, props) => {
    const tabBar = root.querySelector('.filter-tabs');
    const grid = root.querySelector('[data-filter-target]');

    if (!tabBar || !grid || !Array.isArray(props.tabs)) return null;

    // React replaces only the buttons; the cards stay server-rendered.
    return {
      target: tabBar,
      element: (
        <FilterTabs
          tabs={props.tabs}
          attribute={props.attribute}
          match={props.match}
          initial={props.initial}
          grid={grid}
        />
      ),
    };
  },

  faq: (root) => {
    const items = harvestFaqItems(root);
    if (!items.length) return null;

    return { target: root, element: <Faq items={items} /> };
  },

  'inquiry-form': (root) => {
    const form = root.querySelector('form');
    if (!form) return null;

    // Mount into a sibling node so the form's own markup is untouched.
    const mount = document.createElement('div');
    form.parentNode.insertBefore(mount, form);

    return { target: mount, element: <InquiryForm form={form} /> };
  },
};

/**
 * Mount everything on the page.
 */
function hydrateIslands() {
  document.querySelectorAll('[data-ssbd-island]').forEach((root) => {
    const name = root.getAttribute('data-ssbd-island');
    const factory = islands[name];

    if (!factory || root.dataset.ssbdMounted === 'true') return;

    let mount;
    try {
      mount = factory(root, readProps(root));
    } catch (error) {
      // A broken island must never take the rest of the page with it.
      return;
    }

    if (!mount) return;

    root.dataset.ssbdMounted = 'true';
    createRoot(mount.target).render(mount.element);
  });
}

/**
 * Mobile navigation. Small enough that React would be more machinery
 * than the job needs.
 */
function bindNavToggle() {
  const button = document.querySelector('[data-ssbd-nav-toggle]');
  const nav = document.getElementById('site-nav');

  if (!button || !nav) return;

  button.addEventListener('click', () => {
    const open = nav.getAttribute('data-open') === 'true';

    nav.setAttribute('data-open', String(!open));
    button.setAttribute('aria-expanded', String(!open));
  });

  // Close the menu when a link inside it is followed.
  nav.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
      nav.setAttribute('data-open', 'false');
      button.setAttribute('aria-expanded', 'false');
    }
  });
}

/**
 * Fold the mobile submenus away behind a disclosure button.
 *
 * Desktop opens the dropdowns on hover and on focus, which touch has neither
 * of, so the panel used to render every submenu expanded — every service and
 * every product in one scroll. The button opens one branch at a time and
 * leaves the parent link itself alone, because those parents are real pages.
 */
function bindSubmenuToggles() {
  const nav = document.getElementById('site-nav');

  if (!nav) return;

  nav.querySelectorAll('.menu-item-has-children').forEach((item) => {
    const link = item.querySelector(':scope > a');
    const submenu = item.querySelector(':scope > .sub-menu');

    if (!submenu || item.querySelector(':scope > .submenu-toggle')) return;

    const toggle = document.createElement('button');

    toggle.type = 'button';
    toggle.className = 'submenu-toggle';
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute(
      'aria-label',
      link ? `Show ${link.textContent.trim()} submenu` : 'Show submenu'
    );
    toggle.innerHTML = '<span aria-hidden="true">\u25BE</span>';

    toggle.addEventListener('click', () => {
      const open = item.getAttribute('data-open') === 'true';

      // One branch at a time: opening a sibling folds the others, so the
      // panel never grows past a screenful or two.
      if (!open && item.parentElement) {
        item.parentElement
          .querySelectorAll(':scope > li[data-open="true"]')
          .forEach((sibling) => closeBranch(sibling));
      }

      item.setAttribute('data-open', String(!open));
      toggle.setAttribute('aria-expanded', String(!open));
    });

    if (link) {
      link.insertAdjacentElement('afterend', toggle);
    } else {
      item.insertAdjacentElement('afterbegin', toggle);
    }
  });

  // A closed panel reopens folded, rather than wherever it was left.
  const observer = new MutationObserver(() => {
    if (nav.getAttribute('data-open') === 'true') return;

    nav.querySelectorAll('li[data-open="true"]').forEach((item) => closeBranch(item));
  });

  observer.observe(nav, { attributes: true, attributeFilter: ['data-open'] });
}

/**
 * Fold one branch shut, along with anything open inside it.
 *
 * @param {HTMLElement} item Menu item to close.
 */
function closeBranch(item) {
  item.setAttribute('data-open', 'false');

  const toggle = item.querySelector(':scope > .submenu-toggle');

  if (toggle) toggle.setAttribute('aria-expanded', 'false');

  item.querySelectorAll('li[data-open="true"]').forEach((nested) => {
    nested.setAttribute('data-open', 'false');

    const nestedToggle = nested.querySelector(':scope > .submenu-toggle');

    if (nestedToggle) nestedToggle.setAttribute('aria-expanded', 'false');
  });
}

function start() {
  hydrateIslands();
  bindNavToggle();
  bindSubmenuToggles();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start);
} else {
  start();
}
