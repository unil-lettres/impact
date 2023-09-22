import React, { Children } from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";
import { components } from 'react-select';
import _ from 'lodash';

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

const ValueContainer = ({ children, getValue, ...props }) => {
    var length = getValue().length;
    return (
      <components.ValueContainer {...props}>
        {children}
        {length > 1 && (<span className='react-select-badge'>{length} item(s) selected</span>)}
      </components.ValueContainer>
    );
  };

window.MultiFilterSelect = {
    roots: [],
    create() {

        _.each(this.roots, (root) => {
            root.unmount();
        });
        this.roots = [];

        const elements = document.querySelectorAll('.rct-multi-filter-select');
        _.each(elements, (element) => {
            const root = createRoot(element);
            this.roots.push(root);

            const data = element.getAttribute('data');
            const placeholder = element.getAttribute('placeholder');
            root.render(
                <MultiFilterSelect
                    data={data}
                    placeholder={placeholder}
                    refEl={element}
                    reactAttributes={{
                        hideSelectedOptions: false,
                        components: { ValueContainer },
                        styles: {
                            valueContainer: (base, value) => ({
                                ...base,
                                flexWrap: "nowrap",
                            }),
                        }
                    }}
                />
            );
        });

        const element = document.getElementById('rct-multi-filter-select-name');
        if (element) {
            const root = createRoot(element);
            this.roots.push(root);

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
                    refEl={element}
                    reactAttributes={{
                        hideSelectedOptions: false,
                        components: { ValueContainer },
                        styles: {
                            valueContainer: (base, value) => ({
                                ...base,
                                flexWrap: "nowrap",
                            }),
                        }
                    }}
                />
            );
        };
    }
};

window.MultiFilterSelect.create();
