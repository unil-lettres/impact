import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";
import _ from "lodash";

export default class MultiEditorCardSelect extends MultiSelect { }

window.MultiEditorCardSelect = {
    // For keeping track of every react components mounted.
    roots: [],
    element: document.getElementById('rct-multi-editor-card-select'),

    get reference() {
        return this.element.getAttribute('reference');
    },

    get placeholder() {
        return this.element.getAttribute('placeholder');
    },

    get noOptionsMessage() {
        return this.element.getAttribute('noOptionsMessage');
    },

    /**
     * Initialize or reinitialize the react component.
     *
     * @param {string} data JSON string for data. We need to pass data in params
     *                      (unlike other MultiSelect components) because each
     *                      cards has its own default values. So these values
     *                      are dynamic and can't be initialized in the data
     *                      html attribute of the react component at loading.
     */
    create(data) {
        this.destroy();

        if (this.element) {
            const root = createRoot(this.element);
            this.roots.push(root);

            root.render(
                <MultiEditorCardSelect
                    data={data}
                    reference={this.reference}
                    placeholder={this.placeholder}
                    noOptionsMessage={this.noOptionsMessage}
                    refEl={this.element}
                />
            );
        }
    },

    destroy() {
        // Unmount and reinit mounted react components.
        _.each(this.roots, root => root.unmount());
        this.roots = [];

        document.getElementById(this.reference).value = "";
    }
};
