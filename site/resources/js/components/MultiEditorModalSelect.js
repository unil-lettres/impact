import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";
import _ from "lodash";

export default class MultiEditorModalSelect extends MultiSelect {
    select = (record, option) => {
        return new Promise((resolve) => {
            if(option.value) {
                const selectedEvent = new CustomEvent('add-editor', {
                    bubbles: true,
                    cancelable: false,
                    detail: {
                        id: option.value,
                        name: option.label,
                    },
                });
                this.props.refEl.dispatchEvent(selectedEvent);
            }

            resolve();
        });
    }

    remove = (record, option) => {
        return new Promise((resolve) => {
            if(option.value) {
                const selectedEvent = new CustomEvent('remove-editor', {
                    bubbles: true,
                    cancelable: false,
                    detail: {
                        id: option.value,
                        name: option.label,
                    },
                });
                this.props.refEl.dispatchEvent(selectedEvent);
            }

            resolve();
        });
    }
}

window.MultiEditorModalSelect = {
    // For keeping track of every react components mounted.
    roots: [],

    create() {
        // Unmount and reinit mounted react components.
        _.each(this.roots, root => root.unmount());
        this.roots = [];

        const element = document.getElementById('rct-multi-user-select');
        if (element) {
            const root = createRoot(element);
            this.roots.push(root);

            let data = element.getAttribute('data');
            const placeholder = element.getAttribute('placeholder');
            const noOptionsMessage = element.getAttribute('noOptionsMessage');
            root.render(
                <MultiEditorModalSelect
                    data={ data }
                    placeholder={placeholder}
                    noOptionsMessage={noOptionsMessage}
                    noDefaults={true}
                    refEl={element}
                />
            );
        }
    }
};

window.MultiEditorModalSelect.create();