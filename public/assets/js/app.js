/**
 * Biru Payment Hub - App JS
 */
$(document).ready(function() {
    // Copy to clipboard handler
    $(document).on('click', '.btn-copy-clipboard', function(e) {
        e.preventDefault();
        const text = $(this).data('clipboard-text') || $(this).prev('input').val() || $(this).siblings('code').text();
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = $(this).html();
            $(this).html('<i class="bi bi-check-lg"></i> Tersalin!').addClass('btn-success').removeClass('btn-outline-secondary btn-secondary');
            setTimeout(() => {
                $(this).html(originalHtml).removeClass('btn-success').addClass('btn-outline-secondary');
            }, 2000);
        }).catch(err => {
            console.error('Copy failed: ', err);
        });
    });

    // Password visibility toggle
    $('.toggle-password').on('click', function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Auto dismiss flash alerts after 6 seconds
    setTimeout(function() {
        $('.alert.alert-dismissible').fadeOut('slow');
    }, 6000);
});

