import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";

export default class MultiTagFilter extends MultiSelect {
    select = (record, option) => {
        return new Promise((resolve) => {
            const tagSelected = new CustomEvent('add-tag-to-filter', {
                bubbles: true,
                cancelable: false,
                detail: {
                    idFilter: option.value,
                },
            });
            element.dispatchEvent(tagSelected);
            resolve();
        });
    }

    remove = (record, option) => {
        return new Promise((resolve) => {
            const tagSelected = new CustomEvent('remove-tag-to-filter', {
                bubbles: true,
                cancelable: false,
                detail: {
                    idFilter: option.value,
                },
            });
            element.dispatchEvent(tagSelected);
            resolve();
        });
    }
}

const element = document.getElementById('rct-multi-tag-filter');
if (element) {
    const root = createRoot(element);

    let data = element.getAttribute('data');
    let placeholder = element.getAttribute('placeholder');
    root.render(<MultiTagFilter data={data} placeholder={placeholder} />);
}
