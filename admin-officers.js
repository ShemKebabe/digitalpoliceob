
(function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };
  var csrf = '', myId = 0, officers = [], editing = null;

  function api(body) {
    var opts = { credentials: 'same-origin' };
    if (body) {
      opts.method = 'POST';
      opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrf };
      opts.body = new URLSearchParams(body).toString();
    }
    return fetch('admin-officers.php', opts).then(function (r) {
      if (r.status === 401) { location.href = 'login.html'; throw new Error('auth'); }
      if (r.status === 403) { return r.json().then(function (d) { if (d.mustChange) location.href = 'change-password.html'; else if (/role|Access/i.test(d.message || '')) location.href = 'dashboard.html'; throw new Error(d.message || 'Forbidden'); }); }
      return r.json().then(function (d) { if (!r.ok) throw new Error(d.message || 'Request failed'); return d; });
    });
  }

  function show(el, text) { el.textContent = text; el.hidden = !text; }

  function cell(tr, text) { var td = tr.insertCell(); td.textContent = text; return td; }

  function button(label, handler, cls) {
    var b = document.createElement('button');
    b.type = 'button'; b.textContent = label; b.className = 'row-btn ' + (cls || ''); b.addEventListener('click', handler);
    return b;
  }

  function render() {
    var body = $('officersBody'); body.textContent = '';
    if (!officers.length) { var r = body.insertRow(); var c = cell(r, 'No officers yet.'); c.colSpan = 6; c.className = 'empty-row'; return; }
    officers.forEach(function (o) {
      var tr = body.insertRow();
      cell(tr, o.service_number); cell(tr, o.full_name); cell(tr, o.rank);
      var role = cell(tr, ''); var rs = document.createElement('span'); rs.className = 'role ' + (o.role === 'admin' ? 'admin' : ''); rs.textContent = o.role; role.appendChild(rs);
      cell(tr, o.status + (o.pending === '1' || o.pending === 1 ? ' (password pending)' : ''));
      var act = cell(tr, '');
      act.appendChild(button('Edit', function () { openForm(o); }));
      if (o.id !== myId) {
        act.appendChild(button('Reset password', function () { resetPassword(o); }));
        act.appendChild(button(o.status === 'active' ? 'Deactivate' : 'Activate', function () { toggle(o); }, o.status === 'active' ? 'danger' : ''));
      }
    });
  }

  function load() {
    return api().then(function (d) {
      officers = d.officers.map(function (o) { o.id = Number(o.id); return o; });
      show($('pageError'), ''); render();
    }).catch(function (e) { if (e.message !== 'auth') show($('pageError'), 'Could not load officers. ' + e.message); });
  }

  function tempNotice(d, verb) {
    show($('notice'), verb + ' ' + d.service_number + '. Temporary password: ' + d.temp + '  (shown once; the officer must change it at first sign-in).');
  }

  function openForm(o) {
    editing = o || null;
    $('dialogTitle').textContent = o ? 'Edit officer' : 'Add officer';
    $('fNumber').value = o ? o.service_number : ''; $('fNumber').disabled = !!o;
    $('fName').value = o ? o.full_name : ''; $('fRank').value = o ? o.rank : ''; $('fRole').value = o ? o.role : 'officer';
    $('fRole').disabled = !!o && o.id === myId;
    show($('formError'), ''); $('officerDialog').showModal();
  }

  function toggle(o) {
    var next = o.status === 'active' ? 'inactive' : 'active';
    if (next === 'inactive' && !confirm('Deactivate ' + o.full_name + '? They will be signed out and unable to log in. Their records are kept.')) return;
    api({ action: 'update', id: o.id, full_name: o.full_name, rank: o.rank, role: o.role, status: next })
      .then(function () { show($('notice'), ''); return load(); })
      .catch(function (e) { show($('pageError'), e.message); });
  }

  function resetPassword(o) {
    if (!confirm('Reset the password for ' + o.full_name + '?')) return;
    api({ action: 'reset', id: o.id }).then(function (d) { tempNotice(d, 'Password reset for'); return load(); })
      .catch(function (e) { show($('pageError'), e.message); });
  }

  $('addBtn').addEventListener('click', function () { openForm(null); });
  $('cancelBtn').addEventListener('click', function () { $('officerDialog').close(); });
  $('officerForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var data = { full_name: $('fName').value, rank: $('fRank').value, role: $('fRole').value };
    if (editing) { data.action = 'update'; data.id = editing.id; data.status = editing.status; }
    else { data.action = 'create'; data.service_number = $('fNumber').value; }
    api(data).then(function (d) {
      $('officerDialog').close();
      if (!editing) tempNotice(d, 'Created'); else show($('notice'), '');
      return load();
    }).catch(function (err) { show($('formError'), err.message); });
  });

  fetch('me.php', { credentials: 'same-origin' }).then(function (r) { return r.ok ? r.json() : Promise.reject(); })
    .then(function (me) { csrf = me.csrf; myId = me.id; return load(); })
    .catch(function () { location.href = 'login.html'; });

  $('signOutLink').addEventListener('click', function (e) {
    e.preventDefault();
    fetch('logout.php', { method: 'POST', credentials: 'same-origin' }).finally(function () { location.href = 'login.html'; });
  });
})();