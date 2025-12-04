// alerts auto-hide and stacking
document.addEventListener('DOMContentLoaded', function () {
    const alerts = Array.from(document.querySelectorAll('#alerts .alert'));
    alerts.forEach((alertEl, idx) => {
        // stack visually (staggered vertical offset)
        alertEl.style.transform = `translateY(${idx * 70}px)`;

        // auto hide after 4s (+ small stagger)
        const hideDelay = 4000 + idx * 300;
        setTimeout(() => alertEl.classList.add('hide'), hideDelay);

        // remove from DOM after transition ends
        alertEl.addEventListener('transitionend', () => {
            if (alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
        });

        // allow click to dismiss immediately
        alertEl.addEventListener('click', () => alertEl.classList.add('hide'));
    });
});