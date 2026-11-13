(function () {
  'use strict';
  const $ = id => document.getElementById(id);
  const body = $('entriesBody');
  const STATUS = { 'Open': 'status-open', 'Under Investigation': 'status-investigation' };

  $('sidebarToggle').addEventListener('click', () => $('sidebar').classList.toggle('collapsed'));
  $('dashDate').textContent = new Date().toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  $('signOutLink').addEventListener('click', e => {
    e.preventDefault();
    fetch('logout.php').finally(() => { location.href = 'login.html'; });
  });

  const cell = (text, cls) => {
    const td = document.createElement('td');
    if (cls) {
      const span = document.createElement('span');
      span.className = cls; span.textContent = text; td.append(span);
    } else td.textContent = text;
    return td;
  };

  function render(entries) {
    $('count').textContent = entries.length + (entries.length === 1 ? ' entry found' : ' entries found');
    body.replaceChildren(...entries.map(e => {
      const tr = document.createElement('tr');
      const srs = cell(e.srs); srs.className = 'mono';
      tr.append(srs, cell(e.time), cell(e.type, 'tag'), cell(e.location), cell(e.complainant),
                cell(e.status, 'status ' + (STATUS[e.status] || 'status-default')));
      return tr;
    }));
    if (!entries.length) body.innerHTML = '<tr><td colspan="6" class="empty-row">No entries match your search.</td></tr>';
  }

  function load() {
    fetch('ob-entries-data.php?q=' + encodeURIComponent($('search').value.trim()), { credentials: 'same-origin' })
      .then(r => {
        if (r.status === 401) { location.href = 'login.html'; return null; }
        if (!r.ok) throw new Error(r.status);
        return r.json();
      })
      .then(data => data && render(data.entries))
      .catch(() => { $('count').textContent = 'Could not load entries.'; });
  }

  let timer;
  $('search').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(load, 300); });
  load();
})();