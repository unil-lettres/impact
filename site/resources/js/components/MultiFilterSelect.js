import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";

export default class MultiFilterSelect extends MultiSelect {

    select = (record, option) => {
        return new Promise((resolve) => {
            const selectedEvent = new CustomEvent('add-element-to-filter', {
                bubbles: true,
                cancelable: false,
                detail: {
                    filter: option.value ?? option.label,
                    type: record,
                },
            });
            this.props.refEl.dispatchEvent(selectedEvent);
            resolve();
        });
    }

    remove = (record, option) => {
        return new Promise((resolve) => {
            const selectedEvent = new CustomEvent('remove-element-to-filter', {
                bubbles: true,
                cancelable: false,
                detail: {
                    filter: option.value ?? option.label,
                    type: record,
                },
            });
            this.props.refEl.dispatchEvent(selectedEvent);
            resolve();
        });
    }

    create = (record, option) => {
        return new Promise((resolve) => {
            const selectedEvent = new CustomEvent('add-element-to-filter', {
                bubbles: true,
                cancelable: false,
                detail: {
                    filter: option,
                    type: record,
                },
            });
            this.props.refEl.dispatchEvent(selectedEvent);
            resolve();
        });
    }
}

const elements = document.querySelectorAll('.rct-multi-filter-select');
_.each(elements, (element) => {
    const root = createRoot(element);

    const data = element.getAttribute('data');
    const placeholder = element.getAttribute('placeholder');
    root.render(<MultiFilterSelect data={data} placeholder={placeholder} refEl={element} />);
});

const element = document.getElementById('rct-multi-filter-select-name');
if (element) {
    const root = createRoot(element);

    const data = element.getAttribute('data');
    const placeholder = element.getAttribute('placeholder');
    const noOptionsMessage = element.getAttribute('noOptionsMessage');
    const createLabel = element.getAttribute('createLabel');
    root.render(
        <MultiFilterSelect
            data={data}
            canCreate
            createLabel={createLabel}
            noOptionsMessage={noOptionsMessage}
            placeholder={placeholder}
            refEl={element} />
    );
};
