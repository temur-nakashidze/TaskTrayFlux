<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<script>
    (() => {
        const apply = () => {
            for (const key of ['zinc', 'accent']) {
                const value = localStorage.getItem('theme.' + key);
                if (value) document.documentElement.dataset[key] = value;
            }
        };
        apply();
        document.addEventListener('livewire:navigated', apply);
    })();
</script>
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
