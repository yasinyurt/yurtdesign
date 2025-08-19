document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 1000, // Global animation duration
        easing: 'ease-in-out', // Smooth easing
        once: true, // Animations happen only once
        mirror: false // Don't animate when scrolling back up
    });
});