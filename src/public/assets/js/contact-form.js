/**
 * Contact Form Functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get submit button and add loading state
            const submitButton = document.getElementById('contact-submit');
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Sending...';
            submitButton.disabled = true;
            
            // Hide any existing messages
            document.getElementById('contact-success').style.display = 'none';
            document.getElementById('contact-error').style.display = 'none';
            
            // Get form data
            const formData = new FormData(contactForm);
            
            // Send the form data via AJAX
            fetch('api/contact/send.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
                
                if (data.success) {
                    // Show success message
                    const successElement = document.getElementById('contact-success');
                    const successMessage = successElement.querySelector('p');
                    successMessage.textContent = data.message;
                    successElement.style.display = 'block';
                    
                    // Reset the form
                    contactForm.reset();
                    
                    // Scroll to success message
                    successElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    // Show error message
                    const errorElement = document.getElementById('contact-error');
                    const errorMessage = errorElement.querySelector('p');
                    errorMessage.textContent = data.message;
                    errorElement.style.display = 'block';
                    
                    // Scroll to error message
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
                
                // Show generic error message
                const errorElement = document.getElementById('contact-error');
                const errorMessage = errorElement.querySelector('p');
                errorMessage.textContent = 'An error occurred while sending your message. Please try again later.';
                errorElement.style.display = 'block';
                
                // Scroll to error message
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    }
});
