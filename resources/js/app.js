import './bootstrap';

// Desactivar cambio de números al hacer scroll (Chrome/Edge/Safari)
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
