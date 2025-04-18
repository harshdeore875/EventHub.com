document.addEventListener('DOMContentLoaded', function () {
    // Add fade-in animation to all sections
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        section.style.opacity = 0;
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });

    window.addEventListener('scroll', () => {
        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top < window.innerHeight - 100) {
                section.style.opacity = 1;
                section.style.transform = 'translateY(0)';
            }
        });
    });

    // Modal functionality
    const modal = document.querySelector('.modal');
    const closeModal = document.querySelector('.close-modal');

    if (modal && closeModal) {
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Remove dark mode toggle functionality
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.remove();
    }
});
