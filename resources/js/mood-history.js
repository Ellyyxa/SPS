export function initMoodHistory() {
    const canvas = document.querySelector('[data-mood-chart]');
    const source = document.querySelector('#mood-history');
    if (!canvas || !source) return;

    const { mode, categories, series, colors } = JSON.parse(source.textContent);
    const context = canvas.getContext('2d');
    if (!context) return;
    const dailyLabels = [...categories].reverse();
    const categoryIndex = mood => dailyLabels.findIndex(label => label.toLowerCase() === String(mood || '').trim().toLowerCase());

    const draw = () => {
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;
        if (!width || !height) return;
        const ratio = window.devicePixelRatio || 1;
        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);
        context.font = '11px Figtree, sans-serif';

        const left = mode === 'monthly' ? 40 : 58;
        const right = width - 12;
        const top = 22;
        const bottom = height - 46;
        const plotWidth = Math.max(1, right - left);
        const plotHeight = bottom - top;
        const totals = series.map(entry => categories.reduce((sum, mood) => sum + Number(entry.counts?.[mood] || 0), 0));
        const maxCount = Math.max(1, ...totals);
        const yCount = count => bottom - count / maxCount * plotHeight;
        const x = index => mode === 'monthly'
            ? left + (index + .5) * plotWidth / series.length
            : left + index * plotWidth / Math.max(1, series.length - 1);
        const yMood = index => bottom - index * plotHeight / Math.max(1, dailyLabels.length - 1);

        context.fillStyle = '#64748b';
        context.strokeStyle = '#e2e8f0';
        context.lineWidth = 1;
        const ticks = mode === 'monthly'
            ? [...new Set([0, ...[1, 2, 3, 4].map(step => Math.ceil(maxCount * step / 4))])]
            : dailyLabels.map((_, index) => index);
        ticks.forEach(tick => {
            const y = mode === 'monthly' ? yCount(tick) : yMood(tick);
            context.beginPath(); context.moveTo(left, y); context.lineTo(right, y); context.stroke();
            context.textAlign = 'right';
            context.fillText(mode === 'monthly' ? String(tick) : dailyLabels[tick], left - 7, y + 4);
        });

        if (mode === 'monthly') {
            const barWidth = Math.min(24, plotWidth / Math.max(1, series.length) * .65);
            series.forEach((entry, index) => {
                let total = 0;
                categories.forEach(mood => {
                    const count = Number(entry.counts[mood] || 0);
                    context.fillStyle = colors[mood];
                    context.fillRect(x(index) - barWidth / 2, yCount(total + count), barWidth, count / maxCount * plotHeight);
                    total += count;
                });
            });
            context.fillStyle = '#64748b'; context.textAlign = 'left';
            context.fillText('Check-ins', left, 12);
        } else {
            let previous = null;
            series.forEach((entry, index) => {
                const category = categoryIndex(entry.mood);
                if (category < 0) { previous = null; return; }
                const point = { x: x(index), y: yMood(category) };
                context.strokeStyle = '#633ba0'; context.lineWidth = 2;
                if (previous) {
                    context.beginPath(); context.moveTo(previous.x, previous.y);
                    context.lineTo(point.x, point.y); context.stroke();
                }
                context.fillStyle = '#633ba0'; context.beginPath();
                context.arc(point.x, point.y, 3.5, 0, Math.PI * 2); context.fill();
                previous = point;
            });
        }

        // Full server-generated date labels, thinned to avoid mobile collisions.
        const labelCount = Math.max(2, Math.floor(plotWidth / 90));
        const stride = Math.max(1, Math.ceil((series.length - 1) / (labelCount - 1)));
        series.forEach((entry, index) => {
            if (index % stride !== 0 && index !== series.length - 1) return;
            if (index !== 0 && index !== series.length - 1 && series.length - 1 - index < stride) return;
            context.fillStyle = '#64748b';
            context.textAlign = index === 0 ? 'left' : index === series.length - 1 ? 'right' : 'center';
            context.fillText(entry.label, x(index), height - 14);
        });
        const hasRecords = mode === 'monthly' ? totals.some(count => count > 0) : series.some(entry => categoryIndex(entry.mood) >= 0);
        if (!hasRecords) {
            context.fillStyle = '#64748b'; context.textAlign = 'center';
            context.fillText('No mood records in this period', (left + right) / 2, top + plotHeight / 2);
        }
    };
    new ResizeObserver(draw).observe(canvas);
    draw();
}
