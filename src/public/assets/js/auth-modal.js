/**
 * Authentication Modal Functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Auth modal script loaded');
    
    // Modal elements
    const loginModal = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    // Open modal buttons
    const loginButtons = document.querySelectorAll('.open-login-modal');
    const registerButtons = document.querySelectorAll('.open-register-modal');
    
    console.log('Login buttons found:', loginButtons.length);
    console.log('Register buttons found:', registerButtons.length);
    
    // Close buttons
    const closeButtons = document.querySelectorAll('.auth-close');
    
    // Switch between login and register
    const switchToRegister = document.getElementById('switch-to-register');
    const switchToLogin = document.getElementById('switch-to-login');
    
    // Open login modal
    loginButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
    });
    
    // Open register modal
    registerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            registerModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
    });
    
    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (loginModal) loginModal.classList.remove('active');
            if (registerModal) registerModal.classList.remove('active');
            
            // Clear error messages
            const errorElements = document.querySelectorAll('.flash-message');
            errorElements.forEach(element => {
                element.style.display = 'none';
                const errorMessage = element.querySelector('p');
                if (errorMessage) {
                    errorMessage.textContent = '';
                }
            });
            
            // Reset forms
            if (loginForm) loginForm.reset();
            if (registerForm) registerForm.reset();
            
            document.body.style.overflow = ''; // Re-enable scrolling
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === loginModal) {
            loginModal.classList.remove('active');
            document.body.style.overflow = '';
        }
        if (e.target === registerModal) {
            registerModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Switch from login to register
    if (switchToRegister) {
        switchToRegister.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.classList.remove('active');
            registerModal.classList.add('active');
        });
    }
    
    // Switch from register to login
    if (switchToLogin) {
        switchToLogin.addEventListener('click', function(e) {
            e.preventDefault();
            registerModal.classList.remove('active');
            loginModal.classList.add('active');
        });
    }
    
    // Form submission with AJAX and loaders
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = loginForm.querySelector('.auth-form-submit');
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            const formData = new FormData(loginForm);
            
            fetch('api/auth/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
                
                if (data.success) {
                    window.location.href = data.redirect || '/';
                } else {
                    // Show error message
                    const errorElement = document.getElementById('login-error');
                    if (errorElement) {
                        const errorMessage = errorElement.querySelector('p');
                        errorMessage.textContent = data.message || 'Login failed';
                        errorElement.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
                
                const errorElement = document.getElementById('login-error');
                if (errorElement) {
                    const errorMessage = errorElement.querySelector('p');
                    errorMessage.textContent = 'An error occurred. Please try again.';
                    errorElement.style.display = 'block';
                }
            });
        });
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = registerForm.querySelector('.auth-form-submit');
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            const formData = new FormData(registerForm);
            
            fetch('api/auth/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
                
                if (data.success) {
                    // Show success message in login form
                    registerModal.classList.remove('active');
                    
                    // Show login modal with success message
                    loginModal.classList.add('active');
                    
                    // Add success message to login modal
                    const loginSuccessDiv = document.createElement('div');
                    loginSuccessDiv.className = 'flash-message flash-success';
                    loginSuccessDiv.style.display = 'block';
                    loginSuccessDiv.innerHTML = `
                        <div class="flash-content">
                            <i class="fas fa-check-circle"></i>
                            <p>${data.message}</p>
                        </div>
                    `;
                    
                    // Insert success message at the top of the form
                    const loginFormContainer = document.querySelector('#login-modal .auth-modal-right');
                    const loginTitle = loginFormContainer.querySelector('.auth-modal-title');
                    loginFormContainer.insertBefore(loginSuccessDiv, loginTitle.nextSibling);
                    
                    // Reset register form
                    registerForm.reset();
                } else {
                    // Show error message
                    const errorElement = document.getElementById('register-error');
                    if (errorElement) {
                        const errorMessage = errorElement.querySelector('p');
                        errorMessage.textContent = data.message || 'Registration failed';
                        errorElement.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
                
                const errorElement = document.getElementById('register-error');
                if (errorElement) {
                    const errorMessage = errorElement.querySelector('p');
                    errorMessage.textContent = 'An error occurred. Please try again.';
                    errorElement.style.display = 'block';
                }
            });
        });
    }
});
