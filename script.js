// Top du top: multi-step form with validation, recap, AJAX send.
// Step-by-step behavior for beginners is commented.

(function(){
  // Elements
  const form = document.getElementById('quoteForm');
  const steps = Array.from(document.querySelectorAll('.step'));
  const progress = document.getElementById('progress');
  const messages = document.getElementById('formMessages');
  const themeToggle = document.getElementById('themeToggle');

  let current = 0;

  // Show step by index
  function showStep(index){
    steps.forEach((s,i) => {
      const isActive = i === index;
      s.classList.toggle('active', isActive);
      s.setAttribute('aria-hidden', !isActive);
    });

    const percent = Math.round((index) / (steps.length - 1) * 100);
    progress.style.width = percent + '%';
    progress.setAttribute('aria-valuenow', percent);
  }

  // Simple validation for current step
  function validateStep(index){
    const step = steps[index];
    const required = Array.from(step.querySelectorAll('[required]'));
    for (const el of required){
      if (el.type === 'checkbox' || el.type === 'radio'){
        // skip specialized inputs here
      } else if (!el.value || el.value.trim() === ''){
        el.focus();
        showMessage('Complète le champ requis : ' + (el.previousElementSibling ? el.previousElementSibling.innerText : el.name), true);
        return false;
      } else if (el.type === 'email' && !/^\S+@\S+\.\S+$/.test(el.value)){
        el.focus();
        showMessage('Adresse email invalide.', true);
        return false;
      }
    }
    clearMessage();
    return true;
  }

  // Create recap HTML
  function createRecap(){
    const data = new FormData(form);
    const recapEl = document.getElementById('recap');
    // Simple mapping
    const mapping = [
      ['Nom', data.get('nom')],
      ['Email', data.get('email')],
      ['Téléphone', data.get('telephone')],
      ['Prestation', data.get('prestation')],
      ['Budget', data.get('budget')],
      ['Description', data.get('description')]
    ];
    recapEl.innerHTML = mapping.map(row => `<p><strong>${row[0]}:</strong> ${row[1] || '<em>—</em>'}</p>`).join('');
  }

  // Show feedback message
  function showMessage(text, isError){
    messages.textContent = text;
    messages.style.color = isError ? '#b00020' : '';
  }
  function clearMessage(){
    messages.textContent = '';
  }

  // Handle click events on next/prev buttons using event delegation
  document.addEventListener('click', function(e){
    const target = e.target;
    if (target.matches('[data-action="next"]')) {
      if (!validateStep(current)) return;
      if (current < steps.length - 1) {
        current++;
        showStep(current);
        if (current === steps.length - 1) createRecap();
      }
    } else if (target.matches('[data-action="prev"]')) {
      if (current > 0) {
        current--;
        showStep(current);
      }
    }
  });

  // Theme toggle (dark/light)
  themeToggle.addEventListener('click', function(){
    const doc = document.documentElement;
    if (doc.hasAttribute('data-theme')) {
      doc.removeAttribute('data-theme');
      localStorage.removeItem('theme');
    } else {
      doc.setAttribute('data-theme','dark');
      localStorage.setItem('theme','dark');
    }
  });

  // Restore theme
  (function(){
    if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme','dark');
  })();

  // On submit: perform final checks, honeypot, then AJAX send
  form.addEventListener('submit', function(e){
    e.preventDefault();
    // Honeypot check
    if (form.website && form.website.value) {
      // Bot detected - silently fail
      return;
    }
    // Validate last step fields
    if (!validateStep(current)) return;
    // Build FormData (including files)
    const fd = new FormData(form);
    // Send via fetch to send.php
    fetch(form.action, {
      method: 'POST',
      body: fd,
    })
    .then(r => r.json().catch(()=>({ok:false})))
    .then(data => {
      if (data && data.ok) {
        showMessage('Demande envoyée — redirection…');
        window.location.href = 'merci.html';
      } else {
        showMessage(data && data.error ? data.error : 'Erreur lors de l\'envoi. Essaie plus tard.', true);
      }
    })
    .catch(err => {
      console.error(err);
      showMessage('Impossible de contacter le serveur.', true);
    });

  });

  // Initialize
  showStep(current);
})();
