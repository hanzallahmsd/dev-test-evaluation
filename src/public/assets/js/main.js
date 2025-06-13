// Hide preloader when page is fully loaded
window.addEventListener('load', function() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        preloader.classList.add('hidden');
        
        // Remove preloader from DOM after transition
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 500);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Video modal functionality
    const videoThumbnail = document.querySelector('.video-thumbnail');
    const videoModal = document.getElementById('videoModal');
    const closeModal = document.querySelector('.close-modal');
    const videoFrame = document.getElementById('videoFrame');
    
    if (videoThumbnail && videoModal && closeModal && videoFrame) {
        videoThumbnail.addEventListener('click', function() {
            const videoId = this.getAttribute('data-video-id');
            videoFrame.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            videoModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
        
        closeModal.addEventListener('click', function() {
            videoFrame.src = '';
            videoModal.style.display = 'none';
            document.body.style.overflow = ''; // Re-enable scrolling
        });
        
        // Close modal when clicking outside of content
        window.addEventListener('click', function(event) {
            if (event.target === videoModal) {
                videoFrame.src = '';
                videoModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    
    // Billing toggle switch functionality
    const billingToggle = document.getElementById('billing-toggle');
    if (billingToggle) {
        // Function to update pricing cards visibility
        const updatePricingCards = (isYearly) => {
            const monthlyOption = document.querySelector('.billing-option:first-child');
            const yearlyOption = document.querySelector('.billing-option:last-child');
            
            // Update active state on billing options
            if (isYearly) {
                monthlyOption.classList.remove('active');
                yearlyOption.classList.add('active');
            } else {
                monthlyOption.classList.add('active');
                yearlyOption.classList.remove('active');
            }
            
            // Show/hide appropriate pricing cards based on billing interval
            const monthlyCards = document.querySelectorAll('.pricing-card[data-interval="month"]');
            const yearlyCards = document.querySelectorAll('.pricing-card[data-interval="year"]');
            
            // Toggle visibility
            if (isYearly) {
                monthlyCards.forEach(card => card.style.display = 'none');
                yearlyCards.forEach(card => card.style.display = '');
            } else {
                monthlyCards.forEach(card => card.style.display = '');
                yearlyCards.forEach(card => card.style.display = 'none');
            }
        };
        
        // Make sure all cards are visible on page load
        document.querySelectorAll('.pricing-card').forEach(card => {
            card.style.display = '';
        });
        
        // Initial check to ensure correct cards are shown based on toggle state
        setTimeout(() => {
            updatePricingCards(billingToggle.checked);
        }, 100);
        
        // Event listener for toggle changes
        billingToggle.addEventListener('change', function() {
            updatePricingCards(this.checked);
        });
    }
    // Get DOM elements
    const navbar = document.getElementById('navbar');
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');
    const scrollLinks = document.querySelectorAll('.scroll-link');
    
    // Handle navbar scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
            
            // Change hamburger icon
            const icon = navbarToggle.querySelector('i');
            if (navbarMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Smooth scrolling for anchor links
    scrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Close mobile menu if open
            if (navbarMenu && navbarMenu.classList.contains('active')) {
                navbarMenu.classList.remove('active');
                const icon = navbarToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
            
            // Get the target section
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                // Scroll to the target section
                window.scrollTo({
                    top: targetSection.offsetTop - 80, // Offset for navbar height
                    behavior: 'smooth'
                });
                
                // Update URL hash without scrolling
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Scroll reveal animations
    const revealElements = document.querySelectorAll('.feature-card, .pricing-card, .section-header');
    
    function checkReveal() {
        const windowHeight = window.innerHeight;
        const revealPoint = 150;
        
        revealElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            
            if (elementTop < windowHeight - revealPoint) {
                element.classList.add('revealed');
            }
        });
    }
    
    // Initial check
    checkReveal();
    
    // Check on scroll
    window.addEventListener('scroll', checkReveal);
});
