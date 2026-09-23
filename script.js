const togglePassword=document.getElementById('togglePassword');
const passwordInput =document.getElementById('password');
const loginForm=document.getElementById('loginForm');
const errorMessage=document.getElementById('error-message');

togglePassword.addEventListener('click',function(){
    const type=passwordInput.getAttribute('type')==='password'?'text':'password';
    passwordInput.setAttribute('type',type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});