import { useCallback, useEffect, useRef, useState } from 'react';
import config, { t } from '../config.js';

/**
 * Progressive enhancement for the project inquiry form.
 *
 * The <form> itself stays exactly as PHP rendered it and keeps its
 * admin-post.php action, so it still submits with JavaScript off. This
 * component intercepts submit, validates inline, posts to the REST endpoint
 * and renders the status region.
 *
 * @param {object}  props
 * @param {Element} props.form The server-rendered <form>.
 */
export default function InquiryForm({ form }) {
  const [status, setStatus] = useState(null); // { ok, message }
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);
  const statusRef = useRef(null);

  /** Client-side mirror of the server validation, for instant feedback. */
  const validate = useCallback(() => {
    const found = {};

    form.querySelectorAll('[required]').forEach((field) => {
      if (!field.value.trim()) {
        found[field.name] = t('required', 'This field is required.');
      }
    });

    const email = form.querySelector('[type="email"]');
    if (email?.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      found[email.name] = t('invalidEmail', 'Enter a valid email address.');
    }

    return found;
  }, [form]);

  const onSubmit = useCallback(
    async (event) => {
      event.preventDefault();

      const found = validate();
      setErrors(found);

      if (Object.keys(found).length) {
        form.querySelector(`[name="${Object.keys(found)[0]}"]`)?.focus();
        return;
      }

      setSending(true);
      setStatus(null);

      try {
        const response = await fetch(`${config.restUrl}inquiry`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': config.nonce,
          },
          body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
        });

        const payload = await response.json();

        if (payload.ok) {
          setStatus({ ok: true, message: payload.message });
          form.reset();
        } else {
          setErrors(payload.errors || {});
          setStatus({ ok: false, message: payload.message || t('errorGeneric', 'Something went wrong.') });
        }
      } catch (error) {
        // Network failure, or the REST route is blocked. Fall back to the
        // plain POST the form was built to do in the first place.
        form.submit();
        return;
      } finally {
        setSending(false);
      }
    },
    [form, validate]
  );

  // Intercept the real form's submit event.
  useEffect(() => {
    form.addEventListener('submit', onSubmit);
    return () => form.removeEventListener('submit', onSubmit);
  }, [form, onSubmit]);

  // Reflect the submit button's pending state, which lives in server markup.
  useEffect(() => {
    const button = form.querySelector('[type="submit"]');
    if (!button) return;

    button.disabled = sending;
    button.setAttribute('aria-busy', String(sending));
  }, [form, sending]);

  // Paint per-field errors onto the server-rendered fields.
  useEffect(() => {
    form.querySelectorAll('.field .error').forEach((node) => node.remove());

    Object.entries(errors).forEach(([name, message]) => {
      const field = form.querySelector(`[name="${name}"]`);
      if (!field) return;

      field.setAttribute('aria-invalid', 'true');

      const note = document.createElement('span');
      note.className = 'error';
      note.textContent = message;
      field.closest('.field')?.appendChild(note);
    });

    form.querySelectorAll('[aria-invalid]').forEach((field) => {
      if (!errors[field.name]) field.removeAttribute('aria-invalid');
    });
  }, [form, errors]);

  // Move focus to the outcome so screen reader users hear it.
  useEffect(() => {
    if (status) statusRef.current?.focus();
  }, [status]);

  if (!status && !sending) return null;

  if (sending) {
    return <p className="form-status form-status--ok" role="status">{t('sending', 'Sending…')}</p>;
  }

  return (
    <p
      ref={statusRef}
      tabIndex={-1}
      className={`form-status form-status--${status.ok ? 'ok' : 'err'}`}
      role={status.ok ? 'status' : 'alert'}
      style={{ marginBottom: '16px' }}
    >
      {status.message}
    </p>
  );
}
