@script
<script>
    const finderDataObject = {
        /**
         * Contains the list of selected items (ex. 'card-2').
         */
        selectedItems: [],

        /**
         * The last item selected. Useful for shift + click selection.
         */
        lastSelectedItem: null,

        /**
         * Contains the list of opened (expanded) folder.
         */
        openedFolder: [],

        /**
         * Select or deselect the given element and its children.
         */
        toggleSelect(event, element, fromShiftSelection = false) {

            const key = element.getAttribute('data-key')

            if (!fromShiftSelection && this.selectedItems.includes(key)) {

                // Unselect all children of farthest selected parent
                // of clicked element.
                let parent = element.parentNode;
                let farthestSelectedParent = null;

                while (parent) {
                    if (parent?.classList?.contains('folder-selected')) {
                        farthestSelectedParent = parent;
                    }
                    parent = parent.parentNode;
                }

                let _element = farthestSelectedParent || element;
                let key = _element.getAttribute('data-key');
                const keysToUnselect = _.map(
                    _element.querySelectorAll('.finder-folder, .finder-card'),
                    children => children.getAttribute('data-key'),
                );
                _.pull(this.selectedItems, ...keysToUnselect, key);
            } else {
                // Select element and all its children.
                const keysToSelect = _.map(
                    element.querySelectorAll('.finder-folder, .finder-card'),
                    child => child.getAttribute('data-key'),
                );
                this.selectedItems = _.uniq(
                    [...this.selectedItems, ...keysToSelect, key]
                );

                // Select all elements between last selected item and
                // current selected item if shift key is pressed.
                if (!fromShiftSelection && event.shiftKey) {

                    // Get the last selected element only if it's a siblings.
                    const lastSelectedElement = element.parentNode.querySelector(
                        `:scope > [data-key="${this.lastSelectedItem}"]`
                    );

                    // Shift selection works only with elements in the
                    // same folder (siblings).
                    if (lastSelectedElement) {
                        let start = false;

                        // Select all element between last selected item
                        // and current selected item.
                        _.each(element.parentNode.children, child => {
                            if (child === element || child === lastSelectedElement) {
                                start = !start;
                            }
                            if (start) {
                                this.toggleSelect(
                                    event,
                                    child,
                                    child.getAttribute('data-key'),
                                    true,
                                );
                            }
                        });
                    } else {
                        this.lastSelectedItem = key;
                    }
                } else {
                    this.lastSelectedItem = key;
                }
            }

            this.closeAllDropDowns(element);
        },

        /**
         * Expand (open) all folders.
         */
        expandAll() {
            _.each(
                document.querySelectorAll('.finder-folder'),
                element => {
                    const key = element.getAttribute("data-key");
                    if (!this.openedFolder.includes(key)) {
                        this.openedFolder.push(key);
                    }
                },
            );
        },

        /**
         * Expand (open) or collapse (close) the given folder.
         */
        toggleOpen(element, key) {
            this.openedFolder = _.xor(this.openedFolder, [key]);

            // Unselect children if folder is closed and not selected.
            if (!this.selectedItems.includes(key)) {
                _.pull(
                    this.selectedItems,
                    ..._.map(
                        element.closest('li').querySelectorAll('li'),
                        childs => childs.getAttribute('data-key'),
                    ),
                );
            }

            this.closeAllDropDowns(element);
        },

        /**
         * Open the contextual menu for the given item.
         */
        openMenu(element, keepSelection = false) {
            // Close all dropdowns except the one clicked.
            // This is needed to be done manually because some
            // events are not bubbled (preventPropagation).
            this.closeAllDropDowns(element);

            // Unselect items to avoid confusion on the targeted
            // menu actions.
            if (!keepSelection) this.selectedItems = [];
        },

        /**
         * Close all open contextual menu.
         */
        closeAllDropDowns(element = null) {
            document.querySelectorAll(
                '[data-bs-toggle="dropdown"]',
            ).forEach((dropdown) => {
                if (dropdown !== element) {
                    bootstrap.Dropdown.getInstance(dropdown)?.hide();
                }
            });
        },

        /**
         * Select all items.
         */
        selectAll() {
            this.selectedItems = _.map(
                document.querySelectorAll('.finder-folder, .finder-card'),
                children => children.getAttribute('data-key'),
            );
        },

        /**
         * Return if all items are selected.
         */
        isAllSelected() {
            return this.selectedItems.length === document.querySelectorAll('.finder-folder, .finder-card').length;
        },

        /**
         * Open a prompt and aks the user to change the name of the
         * given folder.
         */
        renameFolder(folderId, reloadAfterSave = false) {
            const newName = prompt("{{ trans('courses.finder.menu.rename_prompt') }}");
            if (newName !== null) {
                $wire.call("renameFolder", folderId, newName, reloadAfterSave);
            }
        },

        /**
         * Return if there is cards in the current selection or not.
         */
        hasCardsInSelection() {
            return _.some(this.selectedItems, item => item.startsWith('card-'));
        },

        /**
         * Generate the url to print selected cards.
         */
        generatePrintUrl() {
            const selectedCards = _.filter(
                this.selectedItems,
                item => item.startsWith('card-'),
            );

            if (selectedCards.length > 0) {
                const url = selectedCards.map((item) => {
                    const cardId = item.replace('card-', '');
                    return `cards[]=${cardId}`;
                }).join('&');

                const encodedUrl = encodeURI(url);
                return `{{ route('cards.print') }}?${encodedUrl}`;
            }
        },

        /**
         * Initialize the Sortable plugin.
         */
        initSortable(list) {
            Sortable.create(list, {
                disabled: {{Auth::user()->cannot('massActionsForCardAndFolder', $this->course) ? 'true' : 'false'}},
                onStart: (evt) => {
                    // Display a message if the user cannot move items.
                    if (evt.item.hasAttribute('locked-move')) {
                        this.$dispatch(
                            'flash-message',
                            {
                                lines: ["{{ trans('courses.finder.move.disabled') }}"],
                                bsClass: 'text-bg-danger',
                            },
                        );
                    }

                    // Hide selected elements while dragging.
                    list.closest('.finder').classList.add('hide-select');
                },
                onEnd: () => {
                    // Display again selected elements when dragging end.
                    list.closest('.finder').classList.remove('hide-select');
                },
                onMove: (evt) => {
                    // Disable sorting when item has locked-move attribute.
                    return !evt.dragged.hasAttribute('locked-move');
                },
                onUpdate: (evt) => {
                    _.each(evt.item.parentNode.children, (item, index) => {
                        item.dispatchEvent(new CustomEvent('sort-updated', {
                            bubbles: true,
                            cancelable: false,
                            detail: {
                                id: item.getAttribute('data-id'),
                                type: item.getAttribute('data-type'),
                                position: index,
                            },
                        }));
                    });
                },
                animation: 150,
            });
        }
    };

    Alpine.data('finderData', () => finderDataObject);
</script>
@endscript
