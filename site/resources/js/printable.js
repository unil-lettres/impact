
/**
 * Initialize all element having the attribute data-trigger-print to trigger
 * a new window with the print dialog.
 *
 * The url of the new window is the value of the attribute data-trigger-print.
 */
window.printable = window.printable || {

    /**
     * Initialize all elements having the attribute data-trigger-print to open
     * a new window triggering the print dialog with the value of the attribute
     * as url.
     */
    init: function() {
        const self = this;

        const printButtons = document.querySelectorAll('[data-trigger-print]');
        printButtons.forEach(button => {

            // We use .onclick here (instead of addEventListener) because we don't
            // want to attach an event listener each time we call init.
            button.onclick = function () {
                const url = button.getAttribute('data-trigger-print');
                self.open(url);
            };
        });
    },

    /**
     * Open a new window that trigger the print dialog with the given url as
     * content.
     *
     * @param {string} url
     */
    open(url, target = '_blank', closeAfterPrint = true) {
        const printWindow = window.open(url, target);

        if (closeAfterPrint) {
            // This don't work on Firefox since Firefox trigger afterprint
            // immediatly after the print dialog is opened instead of closed.
            printWindow.addEventListener("afterprint", () => {
                printWindow.close();
            });
        }

        // On Firefox, the print dialog open and close immediatly when the page
        // is loaded. We wait for the window to be loaded before triggering
        // the print dialog.
        printWindow.addEventListener("load", () => {
            printWindow.focus();
            printWindow.print();
        });
    }
};

window.printable.init();
