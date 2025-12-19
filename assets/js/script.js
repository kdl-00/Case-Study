$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('[data-confirm]').click(function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });
    
    // File upload preview
    $('input[type="file"]').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});