@php
    $slides = [
        'radial-gradient(circle at 20% 20%, oklch(0.85 0.12 292), transparent 55%), radial-gradient(circle at 80% 80%, oklch(0.8 0.1 265), transparent 55%), oklch(0.97 0.01 292)',
        'linear-gradient(135deg, oklch(0.82 0.13 300), oklch(0.88 0.1 250) 60%, oklch(0.95 0.02 292))',
        'radial-gradient(circle at 75% 30%, oklch(0.86 0.12 20), transparent 50%), radial-gradient(circle at 25% 75%, oklch(0.82 0.13 292), transparent 55%), oklch(0.97 0.01 280)',
        'linear-gradient(160deg, oklch(0.35 0.05 265), oklch(0.55 0.12 292) 55%, oklch(0.8 0.1 300))',
        'radial-gradient(circle at 30% 70%, oklch(0.85 0.13 160), transparent 50%), radial-gradient(circle at 70% 20%, oklch(0.82 0.12 292), transparent 55%), oklch(0.97 0.01 200)',
        'linear-gradient(120deg, oklch(0.88 0.08 80), oklch(0.85 0.1 292) 60%, oklch(0.9 0.06 265))',
    ];
@endphp

<div
    class="encuesta360-auth-bg"
    x-data="{
        slides: @js($slides),
        current: 0,
        init() {
            this.current = Math.floor(Math.random() * this.slides.length);
            setInterval(() => {
                let next;
                do { next = Math.floor(Math.random() * this.slides.length); } while (next === this.current && this.slides.length > 1);
                this.current = next;
            }, 7000);
        },
    }"
>
    <template x-for="(slide, index) in slides" :key="index">
        <div :style="{ backgroundImage: slide, opacity: current === index ? 1 : 0 }"></div>
    </template>
</div>
