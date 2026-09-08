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
        const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;' }[character]));
        const tasks = JSON.parse(taskData.textContent); let current = new Date(); current.setDate(1);
        const grid = calendar.querySelector('[data-calendar-grid]'); const title = calendar.querySelector('[data-calendar-title]'); const details = calendar.querySelector('[data-calendar-details]');
        const render = () => { const year=current.getFullYear(), month=current.getMonth(), first=new Date(year,month,1).getDay(), days=new Date(year,month+1,0).getDate(); title.textContent=current.toLocaleDateString(undefined,{month:'long',year:'numeric'}); grid.innerHTML=''; for(let slot=0;slot<42;slot++){const day=slot-first+1, cell=document.createElement('button'); cell.type='button'; cell.className='calendar-day'; if(day<1||day>days){cell.classList.add('is-muted');grid.append(cell);continue;} const iso=`${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`; const daily=tasks.filter(t=>t.due_date===iso); const taskPills=daily.slice(0,3).map(t=>`<span class="calendar-task-pill is-${String(t.status).toLowerCase()}" title="${escapeHtml(t.title)}"><span>${escapeHtml(t.title)}</span></span>`).join(''); cell.innerHTML=`<span class="calendar-date-number">${day}</span><span class="calendar-task-stack">${taskPills}</span>`; if(iso===new Date().toISOString().slice(0,10))cell.classList.add('is-today'); cell.addEventListener('click',()=>{details.innerHTML=daily.length?`<p class="font-extrabold text-slate-900">${new Date(iso+'T00:00:00').toLocaleDateString(undefined,{day:'numeric',month:'long',year:'numeric'})}</p>${daily.map(t=>`<article class="mt-4 rounded-xl border border-slate-100 p-3"><p class="font-bold">${escapeHtml(t.title)}</p><p class="mt-1 text-sm text-slate-600">${escapeHtml(t.description||'No description.')}</p><p class="mt-2 text-xs font-bold ${t.status==='Completed'?'text-emerald-600':'text-amber-600'}">${escapeHtml(t.status)} · Score ${escapeHtml(t.priority_score)}</p></article>`).join('')}`:'<p class="text-sm text-slate-500">No tasks are due on this date.</p>';}); grid.append(cell); }}; calendar.querySelector('[data-calendar-prev]').onclick=()=>{current.setMonth(current.getMonth()-1);render();}; calendar.querySelector('[data-calendar-next]').onclick=()=>{current.setMonth(current.getMonth()+1);render();}; render();
    }

    const moodCanvas=document.querySelector('[data-mood-chart]'), moodData=document.querySelector('#mood-history');
    if(moodCanvas&&moodData){
        const rawData=JSON.parse(moodData.textContent);
        const normalizeMood=(value)=>{const mood=String(value||'').trim().toLowerCase(); if(mood.includes('happy')||mood.includes('joy')) return {score:5,label:'Happy'}; if(mood.includes('neutral')||mood.includes('calm')||mood.includes('okay')) return {score:4,label:'Neutral'}; if(mood.includes('sad')||mood.includes('down')) return {score:3,label:'Sad'}; if(mood.includes('stress')||mood.includes('anx')||mood.includes('worry')) return {score:2,label:'Stress'}; if(mood.includes('angry')||mood.includes('mad')||mood.includes('frustrat')) return {score:1,label:'Angry'}; return {score:3,label:value||'Mood'};};
        const data=rawData.map(entry=>({...entry,...normalizeMood(entry.mood)})).reverse(), ctx=moodCanvas.getContext('2d');
        const draw=()=>{const cw=Math.max(moodCanvas.clientWidth,280),ch=Math.max(moodCanvas.clientHeight,240),ratio=window.devicePixelRatio||1,left=52,right=18,top=18,bottom=42; moodCanvas.width=cw*ratio;moodCanvas.height=ch*ratio;ctx.setTransform(ratio,0,0,ratio,0,0);ctx.clearRect(0,0,cw,ch);ctx.font='11px Figtree, sans-serif'; if(!data.length){ctx.fillStyle='#64748b';ctx.textAlign='center';ctx.fillText('No mood records yet',cw/2,ch/2);return;} const labels=['Angry','Stress','Sad','Neutral','Happy'], plotHeight=ch-top-bottom,plotWidth=cw-left-right,yFor=(score)=>ch-bottom-(score-1)*plotHeight/4; ctx.strokeStyle='#dbe3f0';ctx.lineWidth=1;ctx.fillStyle='#64748b';ctx.textAlign='right'; labels.forEach((label,index)=>{const y=yFor(index+1);ctx.beginPath();ctx.moveTo(left,y);ctx.lineTo(cw-right,y);ctx.stroke();ctx.fillText(label,left-8,y+4);}); const xFor=(index)=>data.length===1?left+plotWidth/2:left+index*plotWidth/(data.length-1); ctx.strokeStyle='#633ba0';ctx.lineWidth=3;ctx.beginPath();data.forEach((entry,index)=>{const x=xFor(index),y=yFor(entry.score); index?ctx.lineTo(x,y):ctx.moveTo(x,y);});ctx.stroke();data.forEach((entry,index)=>{const x=xFor(index),y=yFor(entry.score);ctx.fillStyle='#633ba0';ctx.beginPath();ctx.arc(x,y,5,0,Math.PI*2);ctx.fill();ctx.fillStyle='#475569';ctx.textAlign='center';const date=new Date(`${entry.date}T00:00:00`);ctx.fillText(Number.isNaN(date.getTime())?entry.date:date.toLocaleDateString(undefined,{day:'numeric',month:'short'}),x,ch-16);});}; new ResizeObserver(draw).observe(moodCanvas); draw();
    }

    document.querySelectorAll('[data-photo-input]').forEach((input) => input.addEventListener('change', () => { const file=input.files?.[0], preview=document.querySelector(input.dataset.photoPreview); if(file&&preview){preview.src=URL.createObjectURL(file);preview.classList.remove('hidden');} }));
});
