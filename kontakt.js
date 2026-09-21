"use strict";
(() => {
 const form=document.getElementById('contact-form');
 if(!form)return;
 const status=document.getElementById('form-status');
 document.getElementById('contact-fields').disabled=false;
 const body=()=>`Name: ${form.elements.name.value.trim()}
E-Mail: ${form.elements.email.value.trim()}
Telefon: ${form.elements.phone.value.trim() || 'Nicht angegeben'}

${form.elements.message.value.trim()}`;
 form.addEventListener('submit',event=>{
  event.preventDefault();
  if(!form.reportValidity())return;
  const uri='mailto:contact@marcus-knorr-design.de?subject='+encodeURIComponent('Anfrage über die Website')+'&body='+encodeURIComponent(body());
  status.textContent='Bitte senden Sie die E-Mail in Ihrem Mailprogramm ab. Hier wurde noch keine Nachricht versendet. Falls sich kein Programm öffnet, nutzen Sie „Text kopieren“.';
  window.location.href=uri;
 });
 document.getElementById('copy-message').addEventListener('click',async()=>{
  if(!form.reportValidity())return;
  try {
   await navigator.clipboard.writeText(body());
   status.textContent='Text kopiert. Fügen Sie ihn in eine E-Mail an contact@marcus-knorr-design.de ein und senden Sie diese ab.';
  } catch {
   document.getElementById('copy-fallback').hidden=false;
   const area=document.getElementById('prepared-message');area.value=body();area.focus();area.select();
   status.textContent='Bitte den markierten Text manuell kopieren und per E-Mail senden.';
  }
 });
})();
