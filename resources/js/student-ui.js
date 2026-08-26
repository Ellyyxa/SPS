const ready = (callback) => document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', callback)
    : callback();

ready(() => {
    document.querySelectorAll('[data-auto-dismiss]').forEach((flash) => {
        window.setTimeout(() => { flash.classList.add('is-leaving'); window.setTimeout(() => flash.remove(), 350); }, 4200);
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    document.querySelectorAll('form[data-loading]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitter = form.querySelector('[type="submit"]');
            if (submitter) { submitter.disabled = true; submitter.dataset.label = submitter.textContent; submitter.textContent = 'Saving…'; }
            form.classList.add('is-submitting');
        });
    });

    const filter = document.querySelector('[data-task-filter]');
    const sorter = document.querySelector('[data-task-sort]');
    const list = document.querySelector('[data-task-list]');
    const rows = () => [...document.querySelectorAll('[data-task-row]')];
    const applyFilter = () => rows().forEach((row) => row.classList.toggle('is-hidden', !row.dataset.taskSearch.includes(filter?.value.toLowerCase() || '')));
    filter?.addEventListener('input', applyFilter);
    sorter?.addEventListener('change', () => {
        const sorted = rows().sort((a, b) => sorter.value === 'priority' ? Number(b.dataset.taskScore) - Number(a.dataset.taskScore) : Number(a.dataset.taskDue) - Number(b.dataset.taskDue));
        sorted.forEach((row) => list.appendChild(row));
    });

    document.querySelectorAll('[data-mood-option]').forEach((option) => option.addEventListener('change', () => option.closest('label')?.classList.add('is-selected')));
    document.querySelectorAll('[data-xp-fill]').forEach((fill) => requestAnimationFrame(() => { fill.style.width = fill.dataset.xpFill; }));
});
