// ========================================
// GLOBAL APPLICATION JAVASCRIPT
// ========================================

// Global utilities
window.app = {
    // Format currency
    formatCurrency: function(amount) {
        return 'Rp ' + amount. toLocaleString('id-ID');
    },
    
    // Show loading
    showLoading: function(element) {
        if (element) {
            element. classList.add('loading');
        }
    },
    
    // Hide loading
    hideLoading:  function(element) {
        if (element) {
            element. classList.remove('loading');
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        // TODO:  Implement toast notification
        console.log(`${type}: ${message}`);
    }
};

// Initialize global features
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to content
    const content = document.querySelector('main');
    if (content) {
        content.classList.add('fade-in');
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});