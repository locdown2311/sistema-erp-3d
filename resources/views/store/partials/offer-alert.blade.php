<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Record the time the user entered the page
        let lastChecked = new Date().toISOString();
        const storeId = {{ $store->id }};
        const storeSlug = '{{ $store->slug }}';
        
        // Poll every 10 seconds
        setInterval(() => {
            fetch(`/loja/${storeSlug}/oferta-recente?since=${encodeURIComponent(lastChecked)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data && data.has_new) {
                    // Update our marker so we don't alert the same offer again
                    lastChecked = data.offer.created_at;
                    
                    // Show elegant Toast via SweetAlert2
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: true,
                        confirmButtonText: 'Ver Oferta',
                        confirmButtonColor: 'var(--primary)',
                        showCloseButton: true,
                        timer: 8000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: 'info',
                        title: 'Nova Oferta Publicada!',
                        text: data.offer.name,
                        imageUrl: data.offer.image_url,
                        imageWidth: 60,
                        imageHeight: 60,
                        imageAlt: data.offer.name
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `/loja/${storeSlug}/ofertas`;
                        }
                    });
                }
            })
            .catch(error => {
                // Silently fail if offline or error to not disrupt user experience
                // console.log('Polling error:', error);
            });
        }, 10000); // 10 seconds
    });
</script>
