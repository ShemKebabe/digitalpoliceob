// Shared by every officer page: fill the sidebar and show the menu items this role may see.
(function () {
  'use strict';
  fetch('me.php', { credentials: 'same-origin' })
    .then(function (r) {
      if (r.status === 401) { location.href = 'login.html'; return null; }
      return r.json();
    })
    .then(function (me) {
      if (!me) return;
      var set = function (id, text) { var e = document.getElementById(id); if (e) e.textContent = text; };
      set('officerName', me.name);
      set('officerMeta', me.rank + ' • ' + me.ap);

      // <a data-role="admin" hidden> ... shown only when the role matches (the server still enforces it)
      document.querySelectorAll('[data-role]').forEach(function (e) {
        e.hidden = e.dataset.role.split(' ').indexOf(me.role) === -1;
      });
    })
    .catch(function () { /* leave the page as it is; endpoints still protect the data */ });
})();