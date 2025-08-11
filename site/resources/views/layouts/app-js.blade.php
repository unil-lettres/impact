<script type="module">
    $(".with-delete-confirm").on("submit", function(){
        return confirm('{{ trans('messages.confirm.delete') }}');
    });

    $(".with-disable-confirm").on("submit", function(){
        return confirm('{{ trans('messages.confirm.disable') }}');
    });

    $(".with-unlink-confirm").on("submit", function(){
        return confirm('{{ trans('messages.confirm.unlink') }}');
    });

    $(".with-archive-confirm").on("submit", function(){
        return confirm('{{ trans('messages.confirm.archive') }}');
    });

    // Bootstrap 5 tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });

    // Bootstrap 5 popovers
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => {
        new bootstrap.Popover(el);
    });
</script>
