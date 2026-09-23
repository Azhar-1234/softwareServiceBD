import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { t } from '../config.js';

/**
 * Filter bar for a server-rendered card grid.
 *
 * React owns the tab bar and nothing else. The cards themselves stay exactly
 * as PHP rendered them — this component only sets `hidden` on the ones that
 * do not match, so a crawler with JS off (or a crawler that never runs the
 * bundle) still receives every card and every keyword in the markup.
 *
 * @param {object}   props
 * @param {Array}    props.tabs      [{ key, label }]
 * @param {string}   props.attribute Data attribute on each card to match on.
 * @param {string}   props.match     'exact' (default) or 'contains'.
 * @param {string}   props.initial   Tab to open on, e.g. from ?category= in the URL.
 * @param {Element}  props.grid      The [data-filter-target] container.
 */
export default function FilterTabs({ tabs, attribute = 'data-group', match = 'exact', initial = '', grid }) {
  // An initial key that names no tab is ignored rather than filtering the grid
  // down to nothing — the URL is user input like any other.
  const known = tabs.some((tab) => tab.key === initial);
  const [active, setActive] = useState(known ? initial : tabs[0]?.key || 'all');
  const emptyRef = useRef(null);

  const cards = useMemo(
    () => (grid ? Array.from(grid.children).filter((el) => el !== emptyRef.current) : []),
    [grid]
  );

  const matches = useCallback(
    (card) => {
      if (active === 'all') return true;

      const value = card.getAttribute(attribute) || '';
      return match === 'contains' ? value.split(/\s+/).includes(active) : value === active;
    },
    [active, attribute, match]
  );

  useEffect(() => {
    let visible = 0;

    cards.forEach((card) => {
      const show = matches(card);
      card.hidden = !show;
      if (show) visible += 1;
    });

    // Tell assistive tech the grid changed, and say so when nothing matches.
    if (emptyRef.current) emptyRef.current.hidden = visible > 0;
  }, [cards, matches]);

  // Restore every card if this island is ever torn down.
  useEffect(() => () => cards.forEach((card) => { card.hidden = false; }), [cards]);

  return (
    <>
      {tabs.map((tab) => (
        <button
          key={tab.key}
          type="button"
          className="filter-tab"
          data-filter={tab.key}
          aria-pressed={active === tab.key}
          onClick={() => setActive(tab.key)}
        >
          {tab.label}
        </button>
      ))}
      <EmptyState innerRef={emptyRef} grid={grid} />
    </>
  );
}

/**
 * The "nothing matches" notice, portalled into the grid by direct DOM
 * insertion so it participates in the grid's own layout.
 */
function EmptyState({ innerRef, grid }) {
  useEffect(() => {
    if (!grid) return undefined;

    const node = document.createElement('p');
    node.className = 'form-note';
    node.style.gridColumn = '1 / -1';
    node.setAttribute('role', 'status');
    node.textContent = t('noResults', 'Nothing matches that filter yet.');
    node.hidden = true;

    grid.appendChild(node);
    innerRef.current = node;

    return () => {
      node.remove();
      innerRef.current = null;
    };
  }, [grid, innerRef]);

  return null;
}
