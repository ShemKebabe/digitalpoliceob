const togglePassword = document.getElementById('togglePassword');
const passwordInput  = document.getElementById('password');
const loginForm      = document.getElementById('loginForm');
const errorMessage   = document.getElementById('error-message');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? 'visibility' : 'visibility_off';
    });
}

if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        errorMessage.textContent = '';

        const serviceNumber = document.getElementById('serviceNumber').value.trim();
        const password = passwordInput.value;

        if (!serviceNumber || !password) {
            errorMessage.style.color = 'red';
            errorMessage.textContent = 'Please enter both your service number and password';
            return;
        }

        errorMessage.style.color = 'blue';
        errorMessage.textContent = 'Authenticating...';

        const formData = new FormData();
        formData.append('serviceNumber', serviceNumber);
        formData.append('password', password);

        fetch('login.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    errorMessage.style.color = 'green';
                    errorMessage.textContent = 'Login successful, redirecting to dashboard...';
                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 1000);
                } else {
                    errorMessage.style.color = 'red';
                    errorMessage.textContent = data.message || 'Invalid service number or password';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.style.color = 'red';
                errorMessage.textContent = 'A connection error occurred. Please try again later';
            });
    });
}
const sidebar=document.getElementById("sidebar");
const sidebarToggle = document.getElementById('sidebarToggle');
 
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
    });
}
 
function statusClass(status) {
    if (status === 'Open') return 'status-open';
    if (status === 'Under Investigation') return 'status-investigation';
    return 'status-default';
}
 
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
 
function renderEntries(entries) {
    const body = document.getElementById('entriesBody');
    if (!entries || entries.length === 0) {
        body.innerHTML = '<tr><td colspan="5" class="empty-row">No entries recorded yet. Use "New OB Entry" to add one.</td></tr>';
        return;
    }
    body.innerHTML = entries.map(entry => `
        <tr>
            <td class="mono">${escapeHtml(entry.srs)}</td>
            <td>${escapeHtml(entry.time)}</td>
            <td><span class="tag">${escapeHtml(entry.type)}</span></td>
            <td>${escapeHtml(entry.location)}</td>
            <td><span class="status ${statusClass(entry.status)}">${escapeHtml(entry.status)}</span></td>
        </tr>
    `).join('');
}
 
fetch('dashboard-data.php')
    .then(response => {
        if (response.status === 401) {
            window.location.href = 'login.html';
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
 
        document.getElementById('officerName').textContent = data.officer.name;
        document.getElementById('officerMeta').textContent = `${data.officer.rank} • ${data.officer.serviceNumber}`;
        document.getElementById('dutyOfficer').textContent = data.officer.name;
        document.getElementById('dutyRank').textContent = data.officer.rank;
        document.getElementById('dashDate').textContent = data.today;
 
        document.getElementById('statOpenToday').textContent = data.stats.openToday;
        document.getElementById('statTotalFolios').textContent = data.stats.totalFolios;
        document.getElementById('statPendingActions').textContent = data.stats.pendingActions;
        document.getElementById('statMostRecentSrs').textContent = data.stats.mostRecentSrs;
 
        renderEntries(data.entries);
    })
    .catch(error => {
        console.error('Error loading dashboard:', error);
        document.getElementById('entriesBody').innerHTML =
            '<tr><td colspan="5" class="empty-row">Could not load dashboard data. Please try again.</td></tr>';
    });
 
document.getElementById('signOutLink').addEventListener('click', function (e) {
    e.preventDefault();
    fetch('logout.php').then(() => {
        window.location.href = 'login.html';
    });
});
/* incidence report*/
(function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };

  /* ---------- tabs ---------- */
  var tabs = { report: [$('reportbutton'), $('panel-report')], track: [$('trackbutton'), $('panel-track')] };
  function showTab(name) {
    Object.keys(tabs).forEach(function (k) {
      var on = k === name;
      tabs[k][0].classList.toggle('active', on);
      tabs[k][0].setAttribute('aria-selected', on);
      tabs[k][1].hidden = !on;
    });
  }
  tabs.report[0].addEventListener('click', function () { showTab('report'); });
  tabs.track[0].addEventListener('click', function () { showTab('track'); });

  /* ---------- CSRF token ---------- */
  fetch('incident-report-save.php?token=1', { credentials: 'same-origin' })
    .then(function (r) { return r.json(); })
    .then(function (d) { $('csrf_token').value = d.token || ''; })
    .catch(function () { /* handled on submit */ });

  /* ---------- form helpers ---------- */
  var form = $('reportForm');
  var dt = $('incident_datetime');
  function pad(n) { return String(n).padStart(2, '0'); }
  function nowLocal() {
    var d = new Date();
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
  }
  function resetDate() { dt.max = nowLocal(); dt.value = nowLocal(); }
  resetDate();

  function toggleOther(selectId, wrapId, inputId) {
    var sel = $(selectId), wrap = $(wrapId), inp = $(inputId);
    function upd() { var o = sel.value === 'Other'; wrap.hidden = !o; inp.required = o; if (!o) inp.value = ''; }
    sel.addEventListener('change', upd); upd();
  }
  toggleOther('incident_type', 'other_incident_wrap', 'other_incident');
  toggleOther('incident_location', 'other_location_wrap', 'other_location');

  var desc = $('incident_description');
  desc.addEventListener('input', function () { $('descCount').textContent = desc.value.length + ' / 2000'; });

  function setErr(id, msg) {
    var el = $(id); if (!el) return;
    var field = el.closest('.field'); var out = document.querySelector('.err[data-for="' + id + '"]');
    if (field) field.classList.toggle('invalid', !!msg);
    if (out) out.textContent = msg || '';
  }
  function validate() {
    var ok = true, first = null;
    function bad(id, msg) { setErr(id, msg); ok = false; if (!first) first = $(id); }
    ['fName', 'idNumber', 'email', 'phone', 'incident_datetime', 'incident_location', 'other_location', 'incident_type', 'other_incident', 'incident_description'].forEach(function (i) { setErr(i, ''); });

    if ($('fName').value.trim().length < 3) bad('fName', 'Please enter your full name.');
    var idv = $('idNumber').value.trim();
    if (idv && !/^[A-Za-z0-9]{5,20}$/.test(idv)) bad('idNumber', 'Enter a valid ID or passport number.');
    var em = $('email').value.trim();
    if (em && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) bad('email', 'Enter a valid email address.');
    if (!/^(?:\+254|0)[17]\d{8}$/.test($('phone').value.replace(/[\s-]/g, ''))) bad('phone', 'Enter a valid Kenyan number, e.g. 0712345678.');
    if (!dt.value) bad('incident_datetime', 'Select the date and time.');
    else if (dt.value > nowLocal()) bad('incident_datetime', 'The date cannot be in the future.');
    if (!$('incident_location').value) bad('incident_location', 'Select a location.');
    else if ($('incident_location').value === 'Other' && $('other_location').value.trim().length < 3) bad('other_location', 'Please specify the location.');
    if (!$('incident_type').value) bad('incident_type', 'Select the type of incident.');
    else if ($('incident_type').value === 'Other' && $('other_incident').value.trim().length < 3) bad('other_incident', 'Please specify the incident type.');
    if (desc.value.trim().length < 20) bad('incident_description', 'Describe the incident in at least 20 characters.');
    if (first) first.focus();
    return ok;
  }

  function showMsg(text) { var m = $('formMessage'); m.textContent = text; m.className = 'message error'; m.hidden = !text; }

  /* ---------- submit report ---------- */
  form.addEventListener('submit', function (e) {
    e.preventDefault(); showMsg('');
    if (!validate()) return;
    var btn = $('submitBtn'); btn.disabled = true; btn.textContent = 'Submitting...';
    fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (res.ok && res.d.success) {
          $('refNumber').textContent = res.d.reference;
          form.hidden = true; $('successBox').hidden = false;
          document.querySelector('.card-head').scrollIntoView({ behavior: 'smooth' });
        } else {
          if (res.d.errors) Object.keys(res.d.errors).forEach(function (k) { setErr(k, res.d.errors[k]); });
          showMsg(res.d.message || 'Could not submit your report. Please try again.');
          if (res.d.token) $('csrf_token').value = res.d.token;
        }
      })
      .catch(function () { showMsg('Cannot reach the server. Check your connection and that Apache is running.'); })
      .finally(function () { btn.disabled = false; btn.textContent = 'Submit Report'; });
  });

  $('newReport').addEventListener('click', function () {
    form.reset(); resetDate(); $('descCount').textContent = '0 / 2000';
    ['incident_type', 'incident_location'].forEach(function (i) { $(i).dispatchEvent(new Event('change')); });
    $('successBox').hidden = true; form.hidden = false;
    fetch('incident-report-save.php?token=1', { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) { $('csrf_token').value = d.token; });
  });

  $('copyRef').addEventListener('click', function () {
    var t = $('refNumber').textContent, b = this;
    (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function () { b.textContent = 'Copied'; }, function () {
      var r = document.createRange(); r.selectNodeContents($('refNumber')); var s = getSelection(); s.removeAllRanges(); s.addRange(r); b.textContent = 'Select & copy';
    });
    setTimeout(function () { b.textContent = 'Copy'; }, 1800);
  });

  /* ---------- track ---------- */
  var LABELS = { received: 'Received', under_review: 'Under review', in_progress: 'In progress', resolved: 'Resolved', closed: 'Closed' };
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  $('trackForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var box = $('trackResult'), ref = $('track_ob_status').value.trim().toUpperCase();
    box.hidden = false; box.className = 'track-result';
    if (!/^OB\/\d{4}\/\d{2}\/\d{3,5}$/.test(ref)) { box.classList.add('notfound'); box.textContent = 'Enter the reference in the format OB/2026/03/001.'; return; }
    var btn = $('ob_status_button'); btn.disabled = true; box.textContent = 'Searching...';
    fetch('incident-track.php?ref=' + encodeURIComponent(ref), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.found) { box.classList.add('notfound'); box.textContent = d.message || 'No report found with that reference number.'; return; }
        box.innerHTML = '<h3>' + esc(d.reference) + '</h3><dl>' +
          '<dt>Status</dt><dd><span class="status ' + esc(d.status) + '">' + esc(LABELS[d.status] || d.status) + '</span></dd>' +
          '<dt>Incident type</dt><dd>' + esc(d.incident_type) + '</dd>' +
          '<dt>Location</dt><dd>' + esc(d.location) + '</dd>' +
          '<dt>Incident date</dt><dd>' + esc(d.incident_datetime) + '</dd>' +
          '<dt>Submitted</dt><dd>' + esc(d.submitted_at) + '</dd>' +
          (d.remarks ? '<dt>Station remarks</dt><dd>' + esc(d.remarks) + '</dd>' : '') + '</dl>';
      })
      .catch(function () { box.classList.add('notfound'); box.textContent = 'Cannot reach the server. Please try again.'; })
      .finally(function () { btn.disabled = false; });
  });
})();