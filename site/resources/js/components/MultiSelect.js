import React, { Component } from 'react';

import Select from "react-select";
import CreatableSelect from 'react-select/creatable';
import makeAnimated from 'react-select/animated';
import _ from "lodash";

const animatedComponents = makeAnimated();

export default class MultiSelect extends Component {
    constructor(props) {
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            record: data.record,
            options: _.map(
                data.options,
                (option) => ({ value: option.id, label: option.name })
            ),
            values: _.map(
                data.defaults,
                (option) => ({ value: option.id, label: option.name }),
            ),
            isLoading: false,
            isDisabled: data.isDisabled ?? false,
        };
    }

    /**
     * Called when an option is selected from the component.
     * Override to define behavior.
     *
     * @param {Object} record The record given while creating the component.
     * @param {Object} option The option selected from the react select component.
     * @returns A Promise.
     */
    select = (record, option) => {
        return new Promise();
    }

    /**
     * Called when an option is removed from the component.
     * Override to define behavior.
     *
     * @param {Object} record The record given while creating the component.
     * @param {Object} option The option removed from the react select component.
     * @returns A Promise.
     */
    remove = (record, option) => {
        return new Promise();
    }

    /**
     * Called when an option is created from the component.
     * Override to define behavior.
     *
     * @param {Object} record The record given while creating the component.
     * @param {string} name The name of the newly created option.
     * @returns A Promise.
     */
    create = (record, name) => {
        return new Promise();
    }

    handleChange = (selectedOptions, event) => {
        // Available react-select actions: https://github.com/JedWatson/react-select/issues/3451
        const [action, option, getValues] = {
            'select-option': [
                this.select,
                event?.option,
                (prevState) => [...prevState.values, option],
            ],
            'remove-value': [
                this.remove,
                event?.removedValue,
                (prevState) => _.reject(prevState.values, option),
            ],
        }[event.action] || [undefined, undefined, _.identity];

        this.setState({ isLoading: true });

        action(this.state.record, option)
            .then((response) => {
                console.log(response);
                this.setState((prevState) => ({ values: getValues(prevState) }));
            })
            .catch((error) => console.error(error))
            .finally(() => this.setState({ isLoading: false }));
    }

    handleCreate = (inputValue) => {
        this.setState({ isLoading: true });

        this.create(this.state.record, inputValue)
            .then((response) => {
                console.log(response);

                const newTag = {
                    value: response.data.tag_id,
                    label: inputValue,
                };

                this.setState((prevState) => ({
                    options: [
                        ...prevState.options,
                        newTag,
                    ],
                    values: [
                        ...prevState.values,
                        newTag,
                    ]
                }));
            })
            .catch((error) => {

                // Request form validation failed.
                if (error.response.status === 422) {
                    alert(error.response.data.message);
                }

                console.error(error);
            })
            .finally(() => {
                this.setState({ isLoading: false });
            });
    }

    render() {
        let attributes = {
            isMulti: true,
            components: animatedComponents,
            isClearable: false,
            closeMenuOnSelect: false,
            escapeClearsValue: false,
            backspaceRemovesValue: false,
            isLoading: this.state.isLoading,
            value: this.state.values,
            onChange: this.handleChange,
            options: this.state.options,
            isDisabled: this.state.isDisabled,
        };

        if (this.props.placeholder) {
            attributes.placeholder = this.props.placeholder;
        }

        if (this.props.canCreate) {

            if (this.props.createLabel) {
                attributes.formatCreateLabel = (inputValue) => `${this.props.createLabel} "${inputValue}"`;
            }

            attributes.onCreateOption = this.handleCreate;
            return <CreatableSelect {...attributes} />;
        }

        return <Select {...attributes} />;
    }
}
