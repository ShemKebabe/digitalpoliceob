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