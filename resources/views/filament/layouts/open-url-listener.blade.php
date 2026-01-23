@script
<script>
    // Listen untuk event open-url-new-tab dari Livewire
    window.addEventListener('open-url-new-tab', (event) => {
        const url = event.detail.url || event.detail;
        if (url) {
            window.open(url, '_blank');
        }
    });
    
    // Alternatif: Listen langsung di Livewire
    if (window.Livewire) {
        Livewire.on('open-url-new-tab', (url) => {
            window.open(url, '_blank');
        });
    }
</script>
@endscript
