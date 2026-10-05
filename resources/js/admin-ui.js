const whenReady = (callback) => document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', callback)
    : callback();

whenReady(() => {
    document.querySelectorAll('[data-admin-filter]').forEach((form) => {
        const input = form.querySelector('[data-filter-search]');
        input?.addEventListener('input', () => {
            window.clearTimeout(form.filterTimer);
            form.filterTimer = window.setTimeout(() => form.requestSubmit(), 450);
        });
    });

    document.querySelectorAll('[data-admin-chart]').forEach((canvas) => {
        const source = document.querySelector(canvas.dataset.adminChart);
        if (!source) return;
        const values = JSON.parse(source.textContent);
        const labels = ['Angry', 'Stress', 'Sad', 'Neutral', 'Happy'];
        const colors = ['#dc2626', '#ea580c', '#eab308', '#3b82f6', '#7c3aed'];
        const draw = () => {
            const width = Math.max(canvas.clientWidth, 260), height = Math.max(canvas.clientHeight, 210), ratio = window.devicePixelRatio || 1;
            const context = canvas.getContext('2d');
            canvas.width = width * ratio; canvas.height = height * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0); context.clearRect(0, 0, width, height);
            const total = labels.reduce((sum, label) => sum + Number(values[label] || 0), 0);
            if (!total) { context.fillStyle = '#64748b'; context.textAlign = 'center'; context.fillText('No emotion entries yet', width / 2, height / 2); return; }
            const centerX = width / 2, centerY = height / 2 - 4, radius = Math.min(width, height) * .28; let start = -Math.PI / 2;
            labels.forEach((label, index) => { const amount = Number(values[label] || 0); if (!amount) return; const end = start + (amount / total) * Math.PI * 2; context.beginPath(); context.moveTo(centerX, centerY); context.arc(centerX, centerY, radius, start, end); context.closePath(); context.fillStyle = colors[index]; context.fill(); start = end; });
            context.font = '12px Figtree, sans-serif'; context.textAlign = 'left'; labels.forEach((label, index) => { const y = height - 18 * (labels.length - index); context.fillStyle = colors[index]; context.fillRect(14, y - 9, 9, 9); context.fillStyle = '#475569'; context.fillText(`${label}: ${values[label] || 0}`, 29, y); });
        };
        new ResizeObserver(draw).observe(canvas); draw();
    });

    document.querySelectorAll('[data-admin-trend]').forEach((canvas) => {
        const source = document.querySelector(canvas.dataset.adminTrend); if (!source) return;
        const scores = { Angry: 1, Stress: 2, Sad: 3, Neutral: 4, Happy: 5 }, data = JSON.parse(source.textContent);
        const draw = () => { const w=Math.max(canvas.clientWidth,260),h=Math.max(canvas.clientHeight,210),r=window.devicePixelRatio||1,c=canvas.getContext('2d'),l=38,b=30,t=15,rr=12; canvas.width=w*r;canvas.height=h*r;c.setTransform(r,0,0,r,0,0);c.clearRect(0,0,w,h); if(!data.length){c.fillStyle='#64748b';c.fillText('No mood history yet',w/2-45,h/2);return;} c.strokeStyle='#e2e8f0'; for(let i=1;i<=5;i++){const y=h-b-(i-1)*(h-t-b)/4;c.beginPath();c.moveTo(l,y);c.lineTo(w-rr,y);c.stroke();} const x=i=>data.length===1?l+(w-l-rr)/2:l+i*(w-l-rr)/(data.length-1),y=e=>h-b-((scores[e.mood]||3)-1)*(h-t-b)/4;c.strokeStyle='#633ba0';c.lineWidth=3;c.beginPath();data.forEach((e,i)=>i?c.lineTo(x(i),y(e)):c.moveTo(x(i),y(e)));c.stroke();data.forEach((e,i)=>{c.fillStyle='#633ba0';c.beginPath();c.arc(x(i),y(e),4,0,Math.PI*2);c.fill();});}; new ResizeObserver(draw).observe(canvas);draw();
    });

    document.querySelectorAll('[data-admin-photo-input]').forEach((input) => input.addEventListener('change', () => {
        const file = input.files?.[0], preview = document.querySelector(input.dataset.adminPhotoPreview);
        if (file && preview) { preview.src = URL.createObjectURL(file); preview.classList.remove('hidden'); }
    }));

    document.querySelectorAll('[data-notification-form]').forEach((form) => {
        const studentBlock = form.querySelector('[data-student-recipient]');
        const studentSelect = form.querySelector('[data-student-select]');
        const submit = form.querySelector('[data-notification-submit]');
        const updateRecipient = () => {
            const type = form.querySelector('[data-recipient-type]:checked')?.value;
            const sendingToAll = type === 'all';
            studentBlock.hidden = sendingToAll;
            studentSelect.disabled = sendingToAll;
            studentSelect.required = !sendingToAll;
        };
        form.querySelectorAll('[data-recipient-type]').forEach((input) => input.addEventListener('change', updateRecipient));
        form.addEventListener('submit', () => { submit.disabled = true; submit.textContent = 'Sending…'; });
        updateRecipient();
    });
});
