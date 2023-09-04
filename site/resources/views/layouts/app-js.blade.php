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

    // Custom Livewire directive to use js confirm() dialog
    // https://livewire.laravel.com/docs/javascript#registering-custom-directives
    Livewire.directive('confirm', ({ el, directive, component, cleanup }) => {
        let content =  directive.expression

        let onClick = e => {
            if (! confirm(content)) {
                e.preventDefault()
                e.stopImmediatePropagation()
            }
        }

        el.addEventListener('click', onClick, { capture: true })

        cleanup(() => {
            el.removeEventListener('click', onClick)
        })
    })

    document.addEventListener('livewire:init', () => {
        // Customizing Livewire page expiration behavior (avoid confirm() dialog on logout)
        // https://livewire.laravel.com/docs/javascript#customizing-page-expiration-behavior
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault()
                }
            })
        })
    })
</script>
