document.addEventListener('DOMContentLoaded', () => {
    const senhaInput = document.getElementById('senha');
    const toggle = document.querySelector('.toggle-senha');

    if (!senhaInput || !toggle) {
        return;
    }

    toggle.addEventListener('click', () => {
        const mostrando = senhaInput.type === 'text';
        senhaInput.type = mostrando ? 'password' : 'text';
    });
});
