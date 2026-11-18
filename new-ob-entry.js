// New OB entry form. Lookups and the SRS number come from ob-entry.php; the server validates everything again.
(function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };
  var csrf = '';

  function msg(text) { $('formMsg').textContent = text; $('formMsg').hidden = !text; }

  function fill(select, rows) {
    rows.forEach(function (r) { var o = document.createElement('option'); o.value = r.id; o.textContent = r.name; select.appendChild(o); });
  }

  function nowLocal() {
    var d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
  }

  function load() {
    return Promise.all([
      fetch('me.php', { credentials: 'same-origin' }),
      fetch('ob-entry.php', { credentials: 'same-origin' })
    ]).then(function (res) {
      if (res[0].status === 401 || res[1].status === 401) { location.href = 'login.html'; throw new Error('auth'); }
      if (res[1].status === 403) { location.href = 'change-password.html'; throw new Error('auth'); }
      return Promise.all([res[0].json(), res[1].json()]);
    }).then(function (d) {
      var me = d[0], data = d[1];
      csrf = me.csrf;
      $('reporter').textContent = me.name + ' — ' + me.rank + ' (' + me.ap + ')';
      $('srsPreview').textContent = data.srs;
      fill($('location'), data.locations); fill($('type'), data.types);
      $('when').value = nowLocal(); $('when').max = nowLocal();
    });
  }

  $('entryForm').addEventListener('submit', function (e) {
    e.preventDefault(); msg('');
    var body = new URLSearchParams({
      when: $('when').value, location_id: $('location').value, type_id: $('type').value,
      complainant_name: $('cname').value, complainant_id_no: $('cid').value, complainant_phone: $('cphone').value,
      narrative: $('narrative').value
    });
    $('saveBtn').disabled = true;
    fetch('ob-entry.php', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrf },
      body: body.toString()
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (x) {
        if (!x.ok) { msg(x.d.message || 'Could not save the entry.'); $('saveBtn').disabled = false; return; }
        location.href = 'ob-entries.html?saved=' + encodeURIComponent(x.d.srs);
      })
      .catch(function () { msg('A connection error occurred. Please try again later.'); $('saveBtn').disabled = false; });
  });

  load().catch(function (e) { if (e.message !== 'auth') msg('Could not load the form. Reload the page.'); });
})();