<script type="text/javascript">
    $(".with-delete-confirm").on("submit", function(){
        return confirm('{{ trans('messages.confirm.delete') }}');
    });

    $('.base-popover').popover({
        trigger: 'click',
        placement: 'left',
        html: true
    });

    $('body').on('click', function (e) {
        $(".popover").each(function() {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).prevAll('*:first').popover('hide');
            }
        });
    });
</script>
