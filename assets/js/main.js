/**
 * StayNest - Global Client Application JavaScript
 * Handles global notifications, UI interactions, contact form, and helper utilities.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Bootstrap Tooltips & Popovers
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // 2. Navbar Scroll Shadow Effect
    const navbar = document.querySelector('.navbar-staynest');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.classList.add('shadow-sm');
            } else {
                navbar.classList.remove('shadow-sm');
            }
        });
    }

    // 3. Contact Form Submission Handler (Academic Demo with interactive validation & feedback)
    const contactForm = document.getElementById('staynest-contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const nameInput = document.getElementById('contact-name');
            const emailInput = document.getElementById('contact-email');
            const subjectInput = document.getElementById('contact-subject');
            const messageInput = document.getElementById('contact-message');

            if (!nameInput.value.trim() || !emailInput.value.trim() || !messageInput.value.trim()) {
                showToast('Please fill out all required contact fields.', 'warning');
                return;
            }

            const btnSubmit = contactForm.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            btnSubmit.disabled = true;

            setTimeout(() => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
                contactForm.reset();
                showToast('Thank you! Your inquiry has been sent to StayNest support. We will get back to you shortly.', 'success');
            }, 800);
        });
    }
});

/**
 * Global Bootstrap Toast Notification Dispatcher
 * @param {string} message 
 * @param {string} type - 'success' | 'danger' | 'warning' | 'info'
 */
window.showToast = function(message, type = 'success') {
    let container = document.querySelector('.toast-container-custom');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container-custom toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }

    const toastId = 'toast-' + Date.now();
    let bgClass = 'bg-primary text-white';
    let icon = 'bi-info-circle-fill';

    switch (type) {
        case 'success':
            bgClass = 'bg-success text-white';
            icon = 'bi-check-circle-fill';
            break;
        case 'danger':
        case 'error':
            bgClass = 'bg-danger text-white';
            icon = 'bi-exclamation-octagon-fill';
            break;
        case 'warning':
            bgClass = 'bg-warning text-dark';
            icon = 'bi-exclamation-triangle-fill';
            break;
        default:
            bgClass = 'bg-primary text-white';
            icon = 'bi-bell-fill';
    }

    const toastHtml = `
        <div id="${toastId}" class="toast toast-staynest align-items-center ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2 py-3 px-3">
                    <i class="bi ${icon} fs-5"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastElement, { delay: 4000 });
    bsToast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
};
