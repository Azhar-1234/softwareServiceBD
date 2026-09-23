import { useCallback, useEffect, useState } from 'react';
import { readTheme, writeTheme, watchSystemTheme } from '../theme.js';
import { t } from '../config.js';

/**
 * Day / night toggle.
 *
 * This island owns its markup outright — the button carries no content a
 * crawler needs, so React replacing the server-rendered fallback costs
 * nothing.
 */
export default function ThemeToggle() {
  const [theme, setTheme] = useState(readTheme);

  // Keep the document in sync whenever the state changes.
  useEffect(() => {
    writeTheme(theme);
  }, [theme]);

  // Follow the OS until the visitor picks a side themselves.
  useEffect(() => watchSystemTheme(setTheme), []);

  const toggle = useCallback(() => {
    setTheme((current) => (current === 'dark' ? 'light' : 'dark'));
  }, []);

  const isDark = theme === 'dark';

  return (
    <button
      type="button"
      className="theme-toggle"
      onClick={toggle}
      aria-pressed={isDark}
      aria-label={t('toggleTheme', 'Toggle day / night theme')}
      title={t('toggleTheme', 'Toggle day / night theme')}
    >
      <span className="icon" aria-hidden="true">{isDark ? '☀' : '◐'}</span>
      <span className="label">{isDark ? t('day', 'Day') : t('night', 'Night')}</span>
    </button>
  );
}
