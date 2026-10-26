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
