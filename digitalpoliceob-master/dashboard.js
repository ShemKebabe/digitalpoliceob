const sidebar = document.getElementById('sidebar');
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

function showDashError(message) {
    const errBox = document.getElementById('dashError');
    errBox.hidden = false;
    errBox.firstChild.textContent = message + ' ';

    document.getElementById('officerName').textContent = '—';
    document.getElementById('officerMeta').textContent = ' ';
    document.getElementById('dutyOfficer').textContent = '—';
    document.getElementById('dutyRank').textContent = '—';
    document.getElementById('dashDate').textContent = '';
    document.getElementById('statOpenToday').textContent = '—';
    document.getElementById('statTotalFolios').textContent = '—';
    document.getElementById('statPendingActions').textContent = '—';
    document.getElementById('statMostRecentSrs').textContent = '—';
    document.getElementById('entriesBody').innerHTML =
        '<tr><td colspan="5" class="empty-row">Could not load dashboard data.</td></tr>';
}

function hideDashError() {
    document.getElementById('dashError').hidden = true;
}

function loadDashboard() {
    hideDashError();

    fetch('dashboard-data.php')
        .then(response => {
            if (response.status === 401) {
                window.location.href = 'login.html';
                return null;
            }
            if (!response.ok) {
                throw new Error('Server returned status ' + response.status);
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
            showDashError('Could not load dashboard data. Make sure you are accessing this page through XAMPP (http://localhost/...), not a different dev server.');
        });
}

loadDashboard();

const retryBtn = document.getElementById('dashRetry');
if (retryBtn) {
    retryBtn.addEventListener('click', loadDashboard);
}

document.getElementById('signOutLink').addEventListener('click', function (e) {
    e.preventDefault();
    fetch('logout.php').then(() => {
        window.location.href = 'login.html';
    });
});