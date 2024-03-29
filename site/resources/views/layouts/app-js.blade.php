<script type="text/javascript">
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

    // Javascript for tooltips
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip()
    });

    // Javascript for popovers
    $(function () {
        let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        })
    });
</script>
