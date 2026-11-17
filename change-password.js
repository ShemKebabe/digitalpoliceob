(function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };
  var csrf = '', forced = false;

  function msg(text, ok) { var m = $('msg'); m.textContent = text; m.hidden = !text; m.style.color = ok ? '#1f5a2a' : ''; }

  fetch('me.php', { credentials: 'same-origin' })
    .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
    .then(function (me) { csrf = me.csrf; forced = me.mustChange; if (forced) $('hint').textContent = 'You are using a temporary password. Choose a new one to continue. ' + $('hint').textContent; })
    .catch(function () { location.href = 'login.html'; });

  $('pwForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if ($('new').value !== $('confirm').value) return msg('The new passwords do not match.');
    fetch('password-change.php', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrf },
      body: new URLSearchParams({ current: $('current').value, new: $('new').value }).toString()
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (x) {
        if (!x.ok) return msg(x.d.message || 'Could not change password.');
        msg('Password updated. Redirecting...', true);
        setTimeout(function () { location.href = 'dashboard.html'; }, 800);
      }).catch(function () { msg('A connection error occurred. Please try again later.'); });
  });
})();