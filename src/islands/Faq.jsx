import { useState } from 'react';

/**
 * FAQ accordion.
 *
 * The questions and answers are read out of the server-rendered
 * <details> elements rather than passed as props, so the text exists
 * exactly once in the document: no duplicated JSON payload, and the
 * FAQPage JSON-LD emitted by PHP always describes what is really on screen.
 *
 * @param {object} props
 * @param {Array}  props.items [{ q, a }] harvested from the DOM.
 */
export default function Faq({ items }) {
  const [open, setOpen] = useState(0);

  return (
    <div className="faq">
      {items.map((item, index) => {
        const isOpen = open === index;
        const panelId = `ssbd-faq-panel-${index}`;
        const buttonId = `ssbd-faq-button-${index}`;

        return (
          <div className="faq-item" key={buttonId}>
            <h3 style={{ margin: 0 }}>
              <button
                type="button"
                id={buttonId}
                className="faq-q"
                aria-expanded={isOpen}
                aria-controls={panelId}
                onClick={() => setOpen(isOpen ? -1 : index)}
              >
                <span>{item.q}</span>
                <span className="plus" aria-hidden="true">+</span>
              </button>
            </h3>

            <div id={panelId} role="region" aria-labelledby={buttonId} hidden={!isOpen}>
              <p className="faq-a">{item.a}</p>
            </div>
          </div>
        );
      })}
    </div>
  );
}

/**
 * Read the FAQ pairs out of the markup PHP rendered.
 *
 * @param {Element} container The island root.
 * @returns {Array<{q: string, a: string}>}
 */
export function harvestFaqItems(container) {
  return Array.from(container.querySelectorAll('details.faq-item')).map((details) => {
    const question = details.querySelector('.faq-q span:first-child');
    const answer = details.querySelector('.faq-a');

    return {
      q: (question?.textContent || '').trim(),
      a: (answer?.textContent || '').trim(),
    };
  });
}
