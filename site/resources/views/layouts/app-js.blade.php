<script type="text/javascript">
    $(".with-delete-confirm").on("submit", function(){
        return confirm('{{ trans('messages.confirm.delete') }}');
    });
</script>
