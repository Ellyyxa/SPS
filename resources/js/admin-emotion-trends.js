// Aggregate counts only: no student identities, mood notes or numeric mood scores.
export function initAdminEmotionTrends() {
    const form = document.querySelector('[data-emotion-period-form]');
    if (form) {
        const period = form.querySelector('[data-emotion-period]');
        const update = () => {
            form.querySelectorAll('[data-emotion-date]').forEach(input => { input.disabled = period.value !== 'date'; });
            form.querySelectorAll('[data-emotion-custom]').forEach(input => {
                input.disabled = period.value !== 'custom';
                input.required = period.value === 'custom';
            });
        };
        period.addEventListener('change', update);
        update();
    }
    const source = document.querySelector('#admin-emotion-series-data');
    if (!source) return;
    const { mode, categories, series } = JSON.parse(source.textContent);
    const colors = ['#7c3aed', '#3b82f6', '#eab308', '#ea580c', '#dc2626'];
    document.querySelectorAll('[data-admin-emotion-series]').forEach(canvas => {
        const context = canvas.getContext('2d');
        if (!context) return;
        const participation = canvas.dataset.adminEmotionSeries === 'participation';
        const draw = () => {
            const width = Math.max(canvas.parentElement.clientWidth, series.length * 32, 280);
            const height = 240, ratio = window.devicePixelRatio || 1;
            canvas.style.width = `${width}px`;
            canvas.width = width * ratio; canvas.height = height * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, width, height);
            context.font = '11px Figtree, sans-serif';
            const left = 48, right = width - 16, top = 28, bottom = height - 48;
            const maximum = Math.max(1, ...series.map(bucket => participation && mode !== 'monthly' ? bucket.participants : bucket.entries));
            const y = value => bottom - value / maximum * (bottom - top);
            context.fillStyle = '#475569'; context.textAlign = 'left';
            context.fillText(participation ? 'Student / entry count' : 'Check-in entries', left, 14);
            [...new Set([0, 1, 2, 3, 4].map(i => Math.ceil(maximum * i / 4)))].forEach(tick => {
                context.strokeStyle = '#e2e8f0'; context.beginPath(); context.moveTo(left, y(tick)); context.lineTo(right, y(tick)); context.stroke();
                context.fillStyle = '#475569'; context.textAlign = 'right'; context.fillText(String(tick), left - 6, y(tick) + 4);
            });
            const step = (right - left) / Math.max(1, series.length), barWidth = Math.min(26, step * .7);
            const stride = Math.max(1, Math.ceil(series.length / Math.max(2, Math.floor((right - left) / 100))));
            series.forEach((bucket, index) => {
                const x = left + (index + .5) * step;
                if (participation) {
                    const grouped = mode === 'monthly';
                    context.fillStyle = '#7c3aed';
                    context.fillRect(x - barWidth / 2, y(bucket.participants), grouped ? barWidth / 2 : barWidth, bottom - y(bucket.participants));
                    if (grouped) { context.fillStyle = '#3b82f6'; context.fillRect(x, y(bucket.entries), barWidth / 2, bottom - y(bucket.entries)); }
                } else {
                    let sum = 0;
                    categories.forEach((category, categoryIndex) => {
                        const count = bucket.counts[category];
                        context.fillStyle = colors[categoryIndex];
                        context.fillRect(x - barWidth / 2, y(sum + count), barWidth, y(sum) - y(sum + count));
                        sum += count;
                    });
                }
                if (index % stride === 0 || index === series.length - 1) {
                    // Suppress the penultimate label if it crowds the final full-date label.
                    if (index !== 0 && index !== series.length - 1 && series.length - 1 - index < stride) return;
                    context.fillStyle = '#475569';
                    context.textAlign = index === 0 ? 'left' : index === series.length - 1 ? 'right' : 'center';
                    context.fillText(bucket.label, index === 0 ? left : index === series.length - 1 ? right : x, height - 25);
                }
            });
            context.fillStyle = '#475569'; context.textAlign = 'center';
            context.fillText(mode === 'monthly' ? 'Calendar month (Malaysia)' : 'Calendar date (Malaysia)', (left + right) / 2, height - 5);
            if (!series.some(bucket => bucket.entries)) context.fillText('No check-ins recorded', (left + right) / 2, (top + bottom) / 2);
        };
        new ResizeObserver(draw).observe(canvas.parentElement);
        draw();
    });
}
