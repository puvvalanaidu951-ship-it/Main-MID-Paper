document.addEventListener('DOMContentLoaded', function() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            if (!confirm(button.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });
});
