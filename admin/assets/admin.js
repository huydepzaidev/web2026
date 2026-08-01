(() => {
    const body = document.body;
    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => body.classList.toggle('sidebar-open'));
    });
    document.querySelectorAll('[data-dismiss]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.alert')?.remove());
    });
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'Bạn có chắc muốn thực hiện thao tác này?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(button.dataset.copy || '');
            const original = button.textContent;
            button.textContent = 'Đã sao chép';
            setTimeout(() => button.textContent = original, 1200);
        });
    });
    setTimeout(() => document.querySelector('.alert')?.classList.add('fade'), 5000);
})();

