@php
    $slides = [
        'radial-gradient(circle at 20% 20%, #a6ede7, transparent 55%), radial-gradient(circle at 80% 80%, #b9cde6, transparent 55%), #f0fdfb',
        'linear-gradient(135deg, #6cded6, #8aabd2 60%, #eef3f9)',
        'radial-gradient(circle at 75% 30%, #99f6e4, transparent 50%), radial-gradient(circle at 25% 75%, #b9cde6, transparent 55%), #f0fdfb',
        'linear-gradient(160deg, #0f2c52, #159895 55%, #6cded6)',
        'radial-gradient(circle at 30% 70%, #5eead4, transparent 50%), radial-gradient(circle at 70% 20%, #8aabd2, transparent 55%), #ecfdfb',
        'linear-gradient(120deg, #d1f7f3, #a6ede7 60%, #dbe6f2)',
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
