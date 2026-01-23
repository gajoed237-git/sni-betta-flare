@script
<script>
    // Listen untuk Livewire event 'open-url-new-tab'
    document.addEventListener('livewire:initialized', function() {
        if (window.Livewire) {
            Livewire.on('open-url-new-tab', function(url) {
                if (url) {
                    window.open(url, '_blank');
                }
            });
        }
    });
    
    // Fallback untuk custom event
    window.addEventListener('filament:open-url-new-tab', function(e) {
        const url = e.detail?.url;
        if (url) {
            window.open(url, '_blank');
        }
    });
</script>
@endscript
