'use strict';
(() => {
  const form = document.getElementById('contact-form');
  if (!form) return;
  const fields = document.getElementById('contact-fields');
  const status = document.getElementById('form-status');
  const button = document.getElementById('send-button');
  const token = document.getElementById('csrf');
  const message = (text, error = false) => {
    status.textContent = text;
    status.classList.toggle('error', error);
  };
  async function api(options = {}) {
    const response = await fetch('kontakt.php', {
      credentials: 'same-origin', cache: 'no-store', ...options,
      signal: AbortSignal.timeout(60000)
    });
    const type = response.headers.get('content-type') || '';
    if (!type.includes('application/json')) throw new Error('unavailable');
    const data = await response.json();
    return {response, data};
  }
  async function init() {
    try {
      const {response, data} = await api();
      if (!response.ok || !data.ready || !data.csrf) throw new Error('unavailable');
      token.value = data.csrf;
      fields.disabled = false;
      message('');
    } catch (_) {
      message('Der Direktversand ist derzeit nicht verfügbar. Bitte nutzen Sie die E-Mail-Adresse oder Telefonnummer oben.');
    }
  }
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (fields.disabled || button.disabled || !form.reportValidity()) return;
    form.querySelectorAll('[aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
    const body = new URLSearchParams(new FormData(form));
    fields.disabled = true;
    message('Ihre Nachricht wird gesendet …');
    try {
      const {response, data} = await api({method: 'POST', body});
      if (!response.ok || !data.sent) {
        (data.fields || []).forEach(name => {
          const input = form.elements.namedItem(name);
          if (input) input.setAttribute('aria-invalid', 'true');
        });
        message(data.error || 'Die Nachricht konnte nicht versendet werden. Bitte versuchen Sie es später erneut.', true);
      } else {
        form.reset();
        token.value = data.csrf;
        message(data.message);
      }
    } catch (_) {
      message('Die Versandbestätigung konnte nicht empfangen werden. Ihre Eingaben bleiben erhalten. Bitte prüfen Sie Ihre Verbindung oder kontaktieren Sie uns telefonisch, bevor Sie erneut senden.', true);
    } finally { fields.disabled = false; }
  });
  init();
})();
