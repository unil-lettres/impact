import React, { Component } from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import CreatableSelect from 'react-select/creatable';
import makeAnimated from 'react-select/animated';
import _ from "lodash";

const animatedComponents = makeAnimated();

export default class MultiTagSelect extends Component {
    constructor(props) {
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            record: data.record,
            options: _.map(
                data.options,
                (option) => ({ value: option.id, label: option.name })
            ),
            value: _.map(
                data.defaults,
                (option) => ({ value: option.id, label: option.name }),
            ),
            isLoading: false,
        };
    }

    handleChange = (selectedOptions, event) => {
        const cardId = this.state.record.id;

        // Available react-select actions: https://github.com/JedWatson/react-select/issues/3451
        const [action, option, setValue] = {
            'select-option': [
                'link',
                event?.option,
                (prevState) => [...prevState.value, option],
            ],
            'remove-value': [
                'unlink',
                event?.removedValue,
                (prevState) => _.reject(prevState.value, option),
            ],
        }[event.action] || [undefined, undefined];

        this.setState({ isLoading: true });
        axios
            .put(`/cards/${cardId}/${action}/${option.value}`)
            .then((response) => {
                console.log(response);
                this.setState((prevState) => ({ value: setValue(prevState) }));
            })
            .catch((error) => console.error(error))
            .finally(() => this.setState({ isLoading: false }));
    }

    handleCreate = (inputValue) => {
        const cardId = this.state.record.id;

        this.setState({ isLoading: true });
        axios
            .put(`/cards/${cardId}/createTag`, { name: inputValue })
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
                    value: [
                        ...prevState.value,
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
        return (
            <CreatableSelect
                isMulti
                components={animatedComponents}
                isClearable={false}
                closeMenuOnSelect={false}
                escapeClearsValue={false}
                backspaceRemovesValue={false}
                isLoading={this.state.isLoading}
                value={this.state.value}
                onChange={this.handleChange}
                onCreateOption={this.handleCreate}
                formatCreateLabel={(inputValue) => `${this.props.createLabel} "${inputValue}"`}
                options={this.state.options}
            />
        );
    }
}

const element = document.getElementById('rct-multi-tag-select');
if (element) {
    const root = createRoot(element);

    let data = element.getAttribute('data');
    let createLabel = element.getAttribute('createLabel');
    root.render(<MultiTagSelect data={data} createLabel={createLabel} />);
}
