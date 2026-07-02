import React, { Component } from 'react';

import Select from "react-select";
import CreatableSelect from 'react-select/creatable';
import AsyncSelect from 'react-select/async';
import AsyncCreatableSelect from 'react-select/async-creatable';
import makeAnimated from 'react-select/animated';
import _ from "lodash";

const AnimatedComponents = makeAnimated();

export default class MultiSelect extends Component {
    constructor(props) {
        super(props);

        const data = JSON.parse(this.props.data);

        // Don't use defaults values if noDefaults is set to true.
        if (this.props?.noDefaults) {
            data.defaults = [];
        }

        this.state = {
            record: data.record,
            options: _.map(
                data.options,
                option => ({
                    value: option.id,
                    label: option.name,
                    ...(Object.hasOwn(option, 'isFixed') ? { isFixed: option.isFixed } : {}),
                })
            ),
            values: _.map(
                data.defaults,
                option => ({
                    value: option.id,
                    label: option.name,
                    ...(Object.hasOwn(option, 'isFixed') ? { isFixed: option.isFixed } : {}),
                })
            ),
            isLoading: false,
            isDisabled: data.isDisabled ?? false,
            message: data.message ?
                data.message : null,
        };

        this.updateReference(this.state.values);

        // Debounce the loading of options while the user is typing, to
        // avoid firing a request on every keystroke.
        this.debouncedLoadOptions = _.debounce((inputValue, callback) => {
            this.loadOptions(inputValue)
                .then((options) => callback(options))
                .catch((error) => {
                    console.error(error);
                    callback([]);
                });
        }, 300);
    }

    /**
     * Whether the options of this component should be lazily loaded from
     * the server (as the user types) instead of using a static list given
     * upfront. Override to enable asynchronous loading.
     *
     * @returns {boolean}
     */
    isAsync = () => false;

    /**
     * Called to lazily load the options matching the given search term,
     * when isAsync() returns true. Override to define behavior.
     *
     * @param {string} inputValue The term currently typed by the user.
     * @returns A Promise resolving to the list of options.
     */
    loadOptions = (inputValue) => {
        return Promise.resolve(this.state.options);
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
        return Promise.resolve();
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
        return Promise.resolve();
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
        return Promise.resolve();
    }

    printError(message) {
        if(message || null) {
            this.setState({
                message: {
                    type: 'text-danger',
                    content: message,
                }
            });
        }
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
            'deselect-option': [
                this.remove,
                event?.option,
                (prevState) => _.reject(prevState.values, option),
            ],
        }[event.action] || [undefined, undefined, _.identity];

        // Handle reference props.
        this.updateReference(selectedOptions);

        this.setState({
            isLoading: true,
            message: null,
        });

        action(this.state.record, option)
            .then((response) => {
                this.setState((prevState) => ({ values: getValues(prevState) }));
            })
            .catch((error) => console.error(error))
            .finally(() => this.setState({ isLoading: false }));
    }

    updateReference(options) {
        // Handle reference props by setting value on a input HTML element.
        if(this.props.reference) {
            document.getElementById(
                this.props.reference
            ).value = options.map(option => option.value).join(',');
        }
    }

    handleCreate = (inputValue) => {
        this.setState({ isLoading: true });

        this.create(this.state.record, inputValue)
            .then((response) => {
                const newEntity = {
                    value: response?.data?.entity_id,
                    label: inputValue,
                };

                this.setState((prevState) => ({
                    options: [
                        ...prevState.options,
                        newEntity,
                    ],
                    values: [
                        ...prevState.values,
                        newEntity,
                    ]
                }));
            })
            .catch((error) => {
                // Request form validation failed.
                if (error?.response?.status === 422) {
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
            components: {AnimatedComponents},
            isClearable: false,
            closeMenuOnSelect: false,
            escapeClearsValue: false,
            backspaceRemovesValue: false,
            isLoading: this.state.isLoading,
            value: this.state.values,
            onChange: this.handleChange,
            options: this.state.options,
            isDisabled: this.state.isDisabled,
            styles: {
                option: (base, state) => {
                    if (state.data.isExpired) {
                        return { ...base, color: '#6c757d', fontStyle: 'italic' };
                    }
                    return base;
                },
                multiValue: (base, state) => {
                    if (state.data.isFixed) {
                        return { ...base, backgroundColor: 'gray' };
                    }
                    if (state.data.isExpired) {
                        return { ...base, backgroundColor: '#e9ecef', borderColor: '#dee2e6' };
                    }
                    return base;
                },
                multiValueLabel: (base, state) => {
                    if (state.data.isFixed) {
                        return { ...base, fontWeight: 'bold', color: 'white', paddingRight: 6 };
                    }
                    if (state.data.isExpired) {
                        return { ...base, color: '#6c757d', fontStyle: 'italic' };
                    }
                    return base;
                },
                multiValueRemove: (base, state) => {
                    if (state.data.isFixed) {
                        return { ...base, display: 'none' };
                    }
                    if (state.data.isExpired) {
                        return { ...base, color: '#6c757d', ':hover': { backgroundColor: '#dee2e6', color: '#495057' } };
                    }
                    return base;
                },
            },
            ...(this.props.reactAttributes || []),
        };

        if (this.props.placeholder) {
            attributes.placeholder = this.props.placeholder;
        }

        if (this.props.noOptionsMessage) {
            attributes.noOptionsMessage = () => this.props.noOptionsMessage;
        }

        // Lazily load the options from the server as the user types,
        // instead of relying on the static list.
        const isAsync = this.isAsync();
        if (isAsync) {
            delete attributes.options;
            attributes.loadOptions = this.debouncedLoadOptions;
            attributes.cacheOptions = true;
            attributes.defaultOptions = true;
        }

        if (this.props.canCreate) {

            if (this.props.createLabel) {
                attributes.formatCreateLabel = (inputValue) => `${this.props.createLabel} "${inputValue}"`;
            }

            attributes.onCreateOption = this.handleCreate;

            if (isAsync) {
                return <AsyncCreatableSelect {...attributes} />;
            }

            return <CreatableSelect {...attributes} />;
        }

        return(
            <div>
                {isAsync ? <AsyncSelect {...attributes} /> : <Select {...attributes} />}
                {this.state.message && (
                    // Available types: https://getbootstrap.com/docs/5.0/utilities/colors/#colors
                    <div className={this.state.message.type}>{this.state.message.content}</div>
                )}
            </div>
        );
    }
}
