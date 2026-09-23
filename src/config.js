/**
 * Values handed over from PHP via wp_localize_script().
 *
 * Everything has a fallback so the bundle never throws if it is loaded
 * outside the theme (a block editor preview, for instance).
 */

const fallback = {
  restUrl: '/wp-json/ssbd/v1/',
  nonce: '',
  homeUrl: '/',
  i18n: {},
};

const config = { ...fallback, ...(window.ssbdConfig || {}) };
config.i18n = { ...fallback.i18n, ...(window.ssbdConfig?.i18n || {}) };

export default config;

/**
 * Translate a key, falling back to the supplied English string.
 *
 * @param {string} key
 * @param {string} fallbackText
 */
export function t(key, fallbackText) {
  return config.i18n[key] || fallbackText;
}
