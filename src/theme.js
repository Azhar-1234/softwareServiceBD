/**
 * Colour theme state.
 *
 * The <html data-theme> attribute is set by an inline script in header.php
 * before first paint; this module is the same logic, reused for the toggle
 * so the two can never disagree.
 */

const STORAGE_KEY = 'ssbd-theme';

/** Read the stored preference, or fall back to the OS setting. */
export function readTheme() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'dark' || saved === 'light') return saved;
  } catch (e) {
    // Private mode, or site data blocked. Fall through to the OS preference.
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

/** Apply a theme and remember it. */
export function writeTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);

  try {
    localStorage.setItem(STORAGE_KEY, theme);
  } catch (e) {
    // Storage is unavailable; the theme still applies for this page view.
  }
}

/**
 * Watch the OS preference, but only while the visitor has not made an
 * explicit choice of their own.
 *
 * @param {(theme: string) => void} onChange
 * @returns {() => void} Unsubscribe.
 */
export function watchSystemTheme(onChange) {
  const query = window.matchMedia('(prefers-color-scheme: dark)');

  const handler = (event) => {
    let hasExplicitChoice = false;
    try {
      hasExplicitChoice = Boolean(localStorage.getItem(STORAGE_KEY));
    } catch (e) {
      hasExplicitChoice = false;
    }

    if (!hasExplicitChoice) onChange(event.matches ? 'dark' : 'light');
  };

  query.addEventListener('change', handler);
  return () => query.removeEventListener('change', handler);
}
