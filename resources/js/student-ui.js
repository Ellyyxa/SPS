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
    const statusFilter = document.querySelector('[data-task-status]');
    const priorityFilter = document.querySelector('[data-task-priority]');
    const sorter = document.querySelector('[data-task-sort]');
    const list = document.querySelector('[data-task-list]');
    const rows = () => [...document.querySelectorAll('[data-task-row]')];
    const applyFilter = () => rows().forEach((row) => { const matchesSearch=row.dataset.taskSearch.includes(filter?.value.toLowerCase() || ''), matchesStatus=!statusFilter?.value||row.dataset.taskStatusValue===statusFilter.value, matchesPriority=!priorityFilter?.value||row.dataset.taskPriorityValue===priorityFilter.value; row.classList.toggle('is-hidden', !(matchesSearch&&matchesStatus&&matchesPriority)); });
    filter?.addEventListener('input', applyFilter);
    statusFilter?.addEventListener('change', applyFilter);
    priorityFilter?.addEventListener('change', applyFilter);
    sorter?.addEventListener('change', () => {
        const sorted = rows().sort((a, b) => sorter.value === 'priority' ? Number(b.dataset.taskScore) - Number(a.dataset.taskScore) : Number(a.dataset.taskDue) - Number(b.dataset.taskDue));
        sorted.forEach((row) => list.appendChild(row));
    });

    document.querySelectorAll('[data-mood-option]').forEach((option) => option.addEventListener('change', () => option.closest('label')?.classList.add('is-selected')));
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

    const moodCanvas=document.querySelector('[data-mood-chart]'), moodData=document.querySelector('#mood-history');
    if(moodCanvas&&moodData){
        const rawData=JSON.parse(moodData.textContent);
        const normalizeMood=(value)=>{const mood=String(value||'').trim().toLowerCase(); if(mood.includes('happy')||mood.includes('joy')) return {score:5,label:'Happy'}; if(mood.includes('neutral')||mood.includes('calm')||mood.includes('okay')) return {score:4,label:'Neutral'}; if(mood.includes('sad')||mood.includes('down')) return {score:3,label:'Sad'}; if(mood.includes('stress')||mood.includes('anx')||mood.includes('worry')) return {score:2,label:'Stress'}; if(mood.includes('angry')||mood.includes('mad')||mood.includes('frustrat')) return {score:1,label:'Angry'}; return {score:3,label:value||'Mood'};};
        const data = rawData
        .slice(0, 7)
        .map(entry => ({
            ...entry,
            ...normalizeMood(entry.mood)
        }))
        .reverse();

const ctx = moodCanvas.getContext('2d');
        const draw=()=>{const cw=Math.max(moodCanvas.clientWidth,280),ch=Math.max(moodCanvas.clientHeight,240),ratio=window.devicePixelRatio||1,left=52,right=18,top=18,bottom=42; moodCanvas.width=cw*ratio;moodCanvas.height=ch*ratio;ctx.setTransform(ratio,0,0,ratio,0,0);ctx.clearRect(0,0,cw,ch);ctx.font='11px Figtree, sans-serif'; if(!data.length){ctx.fillStyle='#64748b';ctx.textAlign='center';ctx.fillText('No mood records yet',cw/2,ch/2);return;} const labels=['Angry','Stress','Sad','Neutral','Happy'], plotHeight=ch-top-bottom,plotWidth=cw-left-right,yFor=(score)=>ch-bottom-(score-1)*plotHeight/4; ctx.strokeStyle='#dbe3f0';ctx.lineWidth=1;ctx.fillStyle='#64748b';ctx.textAlign='right'; labels.forEach((label,index)=>{const y=yFor(index+1);ctx.beginPath();ctx.moveTo(left,y);ctx.lineTo(cw-right,y);ctx.stroke();ctx.fillText(label,left-8,y+4);}); const xFor=(index)=>data.length===1?left+plotWidth/2:left+index*plotWidth/(data.length-1); ctx.strokeStyle='#633ba0';ctx.lineWidth=3;ctx.beginPath();data.forEach((entry,index)=>{const x=xFor(index),y=yFor(entry.score); index?ctx.lineTo(x,y):ctx.moveTo(x,y);});ctx.stroke();data.forEach((entry,index)=>{const x=xFor(index),y=yFor(entry.score);ctx.fillStyle='#633ba0';ctx.beginPath();ctx.arc(x,y,5,0,Math.PI*2);ctx.fill();ctx.fillStyle='#475569';ctx.textAlign='center';const date=new Date(`${entry.date}T00:00:00`);ctx.fillText(Number.isNaN(date.getTime())?entry.date:date.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short'
})  ,x,ch-16);});}; new ResizeObserver(draw).observe(moodCanvas); draw();
    }

    document.querySelectorAll('[data-photo-input]').forEach((input) => input.addEventListener('change', () => { const file=input.files?.[0], preview=document.querySelector(input.dataset.photoPreview); if(file&&preview){preview.src=URL.createObjectURL(file);preview.classList.remove('hidden');} }));
});
