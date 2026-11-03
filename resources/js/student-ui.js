import { initMoodHistory } from './mood-history';

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

    const taskWorkspace = document.querySelector('[data-task-workspace]');
    if (taskWorkspace) {
        const search = taskWorkspace.querySelector('[data-task-filter]');
        const statuses = [...taskWorkspace.querySelectorAll('[data-task-status]')];
        const priority = taskWorkspace.querySelector('[data-task-priority]');
        const sorter = taskWorkspace.querySelector('[data-task-sort]');
        const list = taskWorkspace.querySelector('[data-task-list]');
        const cards = [...list.querySelectorAll('[data-task-row]')];
        const updateTasks = () => {
            const query = search.value.trim().toLocaleLowerCase();
            const status = statuses.find((input) => input.checked)?.value || '';
            let visible = 0;
            cards.forEach((card) => {
                const matches = card.dataset.taskSearch.toLocaleLowerCase().includes(query)
                    && (!status || card.dataset.taskStatusValue === status)
                    && (!priority.value || card.dataset.taskPriorityValue === priority.value);
                card.hidden = !matches;
                if (matches) visible++;
            });
            const sorted = [...cards].sort((a, b) => {
                if (sorter.value === 'priority') return Number(b.dataset.taskScore) - Number(a.dataset.taskScore);
                const difference = Number(a.dataset.taskDue) - Number(b.dataset.taskDue);
                return sorter.value === 'due-desc' ? -difference : difference;
            });
            sorted.forEach((card) => list.appendChild(card));
            taskWorkspace.querySelector('[data-task-empty]').hidden = visible > 0;
            taskWorkspace.querySelector('[data-task-result]').textContent = `${visible} of ${cards.length} tasks`;
        };
        search.addEventListener('input', updateTasks);
        [...statuses, priority, sorter].forEach((control) => control.addEventListener('change', updateTasks));
        taskWorkspace.querySelector('[data-task-reset]')?.addEventListener('click', () => {
            search.value = '';
            statuses[0].checked = true;
            priority.value = '';
            updateTasks();
        });
        updateTasks();
    }

    document.querySelectorAll('[data-mood-option]').forEach((option) => option.addEventListener('change', () => option.closest('label')?.classList.add('is-selected')));
    const checkInForm = document.querySelector('[data-checkin-form]');
    if (checkInForm) {
        const dateSelect = checkInForm.querySelector('[data-checkin-date]');
        const save = checkInForm.querySelector('[data-checkin-save]');
        const status = checkInForm.querySelector('[data-checkin-status]');
        const updateDateStatus = () => {
            const option = dateSelect.selectedOptions[0];
            const recorded = option?.dataset.recorded === 'true';
            const unavailable = !option || option.dataset.unavailable === 'true';
            save.disabled = recorded || unavailable;
            status.textContent = recorded
                ? 'Already checked in for this date. Choose another available day.'
                : unavailable ? 'This date is no longer eligible. Please choose Today or Yesterday.'
                    : 'This date is available for check-in.';
        };
        dateSelect.addEventListener('change', updateDateStatus);
        checkInForm.addEventListener('submit', (event) => {
            updateDateStatus();
            if (save.disabled) event.preventDefault();
        });
        updateDateStatus();
    }
    document.querySelectorAll('[data-xp-fill]').forEach((fill) => requestAnimationFrame(() => { fill.style.width = fill.dataset.xpFill; }));
    document.querySelectorAll('[data-password-toggle]').forEach((button) => button.addEventListener('click', () => { const input = document.querySelector(button.dataset.passwordToggle); if (!input) return; const visible = input.type === 'text'; input.type = visible ? 'password' : 'text'; button.textContent = visible ? 'Show' : 'Hide'; button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password'); }));

    const calendar = document.querySelector('[data-calendar]');
const taskData = document.querySelector('#calendar-tasks');

if (calendar && taskData) {

    const escapeHtml = (value) =>
        String(value).replace(/[&<>'"]/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[character]));


    const tasks = JSON.parse(taskData.textContent);

    let current = new Date();
    current.setDate(1);


    const grid = calendar.querySelector('[data-calendar-grid]');
    const title = calendar.querySelector('[data-calendar-title]');
    const details = calendar.querySelector('[data-calendar-details]');

    const previousButton = calendar.querySelector('[data-calendar-prev]');
    const nextButton = calendar.querySelector('[data-calendar-next]');
    const todayButton = calendar.querySelector('[data-calendar-today]');


    const getTodayIso = () => {
        const today = new Date();

        return [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, '0'),
            String(today.getDate()).padStart(2, '0')
        ].join('-');
    };


    const showDateDetails = (iso, daily) => {

        const selectedDate = new Date(`${iso}T00:00:00`);

        const formattedDate = selectedDate.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });


        if (!daily.length) {

            details.innerHTML = `
                <p class="font-extrabold text-slate-900">
                    ${formattedDate}
                </p>

                <p class="mt-4 text-sm text-slate-500">
                    No tasks are due on this date.
                </p>
            `;

            return;
        }


        details.innerHTML = `
            <p class="font-extrabold text-slate-900">
                ${formattedDate}
            </p>

            ${daily.map(task => `

                <article class="mt-4 rounded-xl border border-slate-100 p-3">

                    <p class="font-bold">
                        ${escapeHtml(task.title)}
                    </p>

                    <p class="mt-1 text-sm text-slate-600">
                        ${escapeHtml(task.description || 'No description.')}
                    </p>

                    <p class="mt-2 text-xs font-bold ${
                        task.status === 'Completed'
                            ? 'text-emerald-600'
                            : 'text-amber-600'
                    }">
                        ${escapeHtml(task.status)}
                        · Score ${escapeHtml(task.priority_score)}
                    </p>

                </article>

            `).join('')}
        `;
    };


    const render = () => {

        const year = current.getFullYear();
        const month = current.getMonth();

        const firstDay = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();

        const todayIso = getTodayIso();


        title.textContent = current.toLocaleDateString('en-GB', {
            month: 'long',
            year: 'numeric'
        });


        grid.innerHTML = '';


        for (let slot = 0; slot < 42; slot++) {

            const day = slot - firstDay + 1;

            const cell = document.createElement('button');

            cell.type = 'button';
            cell.className = 'calendar-day';


            if (day < 1 || day > totalDays) {

                cell.classList.add('is-muted');

                grid.append(cell);

                continue;
            }


            const iso = [
                year,
                String(month + 1).padStart(2, '0'),
                String(day).padStart(2, '0')
            ].join('-');


            const daily = tasks.filter(task => task.due_date === iso);

            const pendingCount = daily.filter(
                task => task.status === 'Pending'
            ).length;

            const completedCount = daily.filter(
                task => task.status === 'Completed'
            ).length;


            let indicators = '';


            if (pendingCount > 0) {

                indicators += `
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        ${pendingCount}
                    </span>
                `;
            }


            if (completedCount > 0) {

                indicators += `
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        ${completedCount}
                    </span>
                `;
            }


            cell.innerHTML = `
                <span class="calendar-date-number">
                    ${day}
                </span>

                <span class="mt-1 flex flex-wrap justify-center gap-2">
                    ${indicators}
                </span>
            `;


            if (iso === todayIso) {
                cell.classList.add('is-today');
            }


            cell.addEventListener('click', () => {

                calendar
                    .querySelectorAll('.calendar-day')
                    .forEach(dayCell => {
                        dayCell.classList.remove('is-selected');
                    });

                cell.classList.add('is-selected');

                showDateDetails(iso, daily);
            });


            grid.append(cell);
        }
    };


    previousButton?.addEventListener('click', () => {
        current.setMonth(current.getMonth() - 1);
        render();
    });


    nextButton?.addEventListener('click', () => {
        current.setMonth(current.getMonth() + 1);
        render();
    });


    todayButton?.addEventListener('click', () => {

        const today = new Date();

        current = new Date(
            today.getFullYear(),
            today.getMonth(),
            1
        );

        render();


        const todayIso = getTodayIso();

        const daily = tasks.filter(
            task => task.due_date === todayIso
        );

        showDateDetails(todayIso, daily);
    });


    render();
}   

    initMoodHistory();

    document.querySelectorAll('[data-photo-input]').forEach((input) => input.addEventListener('change', () => { const file=input.files?.[0], preview=document.querySelector(input.dataset.photoPreview); if(file&&preview){preview.src=URL.createObjectURL(file);preview.classList.remove('hidden');} }));
});
