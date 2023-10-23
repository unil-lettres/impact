import React, { useState } from 'react';
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
            resolve({ data: { entity_id: option } });
        });
    }
}

/**
 * Used to customize the input of the react select.
 * It will display the number of selected element at the end of the input.
 * It is useful to set a fixed width and still knowing that items are selected.
 *
 * It is best used with "hideSelectedOptions: false" that highlight the selected
 * items in the list instead of hiding them when they are selected.
 */
const ValueContainer = ({ children, getValue, ...props }) => {
    const length = getValue().length;
    const label = document.querySelector("[data-filter-label]").getAttribute('data-filter-label');
    return (
        <components.ValueContainer {...props}>
        {children}
        {length > 1 && (<span className='react-select-badge'>{length} {label}</span>)}
        </components.ValueContainer>
    );
};

/**
 * Used to customize the dropdown of the react select.
 *
 * It adds checkboxes at the end for chosing in which card's boxes we want to
 * make the filter search in.
 */
const MenuList = props => {
    const checkedFilters = window.MultiFilterSelect.checkedFilter;

    function handleFilterChange(event) {
        dispatchToggleFilter(event.target.value, event.target.checked);
    }

    function dispatchToggleFilter(filter, checked) {
        const toggleEvent = new CustomEvent('toggle-filter-search-box', {
            bubbles: true,
            cancelable: false,
            detail: {
                filter: filter,
                checked: checked,
            },
        });
        checkedFilters[filter] = checked;
        setCheckedStates({...checkedFilters});
        window.dispatchEvent(toggleEvent);
    }

    const [checkedStates, setCheckedStates] = useState({...checkedFilters});

    function getFilterComponent(filterName, label) {
        return (
            <div className="form-check" key={filterName}>
                <input
                    onChange={handleFilterChange}
                    checked={checkedStates[filterName]}
                    className="form-check-input"
                    type="checkbox"
                    value={filterName}
                ></input>
                <label
                    className="form-check-label"
                    onClick={() => dispatchToggleFilter(filterName, !checkedStates[filterName])}
                >
                    {label}
                </label>
            </div>
        );
    }

    const {name, ...boxes} = checkedFilters;
    const boxesComponents = _.map(
        boxes,
        (__, filterName) => getFilterComponent(
            filterName,
            // Box2 => 2
            filterName.replace(/[^0-9]/g, ""),
        ),
    );
    return (
        <components.MenuList {...props}>
            {props.children}
            <div className="d-flex gap-2 justify-content-evenly flex-column flex-xl-row px-2 pt-1 border-top">
                <div>
                    {getFilterComponent('name', window.MultiFilterSelect.dataNameLabel)}
                </div>
                <div className='d-flex flex-column flex-lg-row gap-2'>
                    <div>{window.MultiFilterSelect.dataBoxLabel} :</div>
                    {boxesComponents}
                </div>
            </div>
        </components.MenuList>
    );
};

window.MultiFilterSelect = {
    roots: [],

    // We need to keep checked filter outside of MenuList component cause
    // it is destroyed each time the menu is closed.
    checkedFilter: {},

    dataNameLabel: '',
    dataBoxLabel: '',
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
            this.dataNameLabel = element.getAttribute('data-name-label');
            this.dataBoxLabel = element.getAttribute('data-box-label');

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
                        components: { ValueContainer, MenuList },
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
