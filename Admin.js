(function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };

  // Build elements with textContent, never innerHTML, so database text can't inject markup (XSS).
  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined && text !== null) e.textContent = text;
    return e;
  }
  function fill(id, nodes, emptyText) {
    var box = $(id);
    box.replaceChildren();
    if (nodes === null) { box.append(el('p', 'muted', 'This section could not be loaded.')); return; }
    if (!nodes.length) { box.append(el('p', 'muted', emptyText)); return; }
    box.append.apply(box, nodes);
  }

  /* ---------- page chrome ---------- */
  $('dashDate').textContent = new Date().toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  $('sidebarToggle').addEventListener('click', function () {
    document.querySelector('.dash-layout').classList.toggle('nav-collapsed');
  });
  $('signOutLink').addEventListener('click', function (e) {
    e.preventDefault();
    fetch('logout.php', { credentials: 'same-origin' }).finally(function () { location.href = 'login.html'; });
  });

  /* ---------- renderers ---------- */
  function bars(list) {
    if (list === null) return null;
    var max = Math.max.apply(null, list.map(function (r) { return +r.total; }).concat(1));
    return list.map(function (r) {
      var row = el('div', 'bar-row');
      var bar = el('div', 'bar'), fillEl = el('span');
      fillEl.style.width = (r.total / max * 100) + '%';
      bar.append(fillEl);
      row.append(el('span', null, r.label), bar, el('span', 'count', r.total));
      return row;
    });
  }
  function officerRows(list) {
    if (list === null) return null;
    return list.map(function (o) {
      var tr = el('tr'), role = el('td');
      role.append(el('span', 'role ' + o.role, o.role));
      var ap = el('td'); ap.append(el('code', null, o.ap_number));
      tr.append(ap, el('td', null, o.full_name), el('td', null, o.rank), el('td', null, o.station), role);
      return tr;
    });
  }
  function auditItems(list) {
    if (list === null) return null;
    return list.map(function (a) {
      var li = el('li');
      var when = new Date(String(a.created_at).replace(' ', 'T'));
      li.append(el('strong', null, a.full_name || 'Unknown officer'), document.createTextNode(' — ' + a.action),
        el('small', null, a.details || ''), el('small', null, isNaN(when) ? '' : when.toLocaleString('en-GB')));
      return li;
    });
  }

  function render(d) {
    var s = d.stats;
    $('statRecords').textContent  = s.records  === null ? '—' : s.records;
    $('statOfficers').textContent = s.officers === null ? '—' : s.officers;
    $('statPublic').textContent   = s.public   === null ? '—' : s.public;
    $('statAudit').textContent    = s.audit    === null ? '—' : s.audit;
    fill('statusBars', bars(d.status), 'No entries yet.');
    fill('typeBars', bars(d.types), 'No entries yet.');
    var tb = $('officersBody'); tb.replaceChildren();
    var rows = officerRows(d.officers);
    if (!rows || !rows.length) { var tr = el('tr'), td = el('td', 'empty-row', rows ? 'No officers.' : 'This section could not be loaded.'); td.colSpan = 5; tr.append(td); tb.append(tr); }
    else tb.append.apply(tb, rows);
    var au = $('auditList'); au.replaceChildren();
    var items = auditItems(d.audit);
    if (!items || !items.length) au.append(el('li', 'muted', items ? 'No activity yet.' : 'This section could not be loaded.'));
    else au.append.apply(au, items);
  }

  /* ---------- load ---------- */
  function load() {
    $('adminError').hidden = true;
    fetch('admin-data.php', { credentials: 'same-origin' })
      .then(function (r) {
        if (r.status === 401) { location.href = 'login.html'; return null; }
        return r.json().then(function (d) { return { status: r.status, d: d }; });
      })
      .then(function (res) {
        if (!res) return;
        if (res.status === 403) { $('adminError').firstChild.textContent = 'Administrators only. '; $('adminError').hidden = false; $('adminRetry').hidden = true; return; }
        if (res.status !== 200) throw new Error(res.d.message || 'Request failed');
        render(res.d);
      })
      .catch(function () { $('adminError').hidden = false; });
  }
  $('adminRetry').addEventListener('click', load);
  load();
})();