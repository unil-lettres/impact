import React, { Component } from 'react';

import Select from "react-select";
import makeAnimated from 'react-select/animated';

const AnimatedComponents = makeAnimated();

export default class SingleSelect extends Component {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            options: [],
            default: [],
            selected: [],
            clearable: data.clearable ?
                data.clearable : false,
            disabled: data.disabled ?
                data.disabled : false,
            message: data.message ?
                data.message : null,
            isOptionSelected: false
        };

        this.state.selected = this.state.default;
    }

    handleChange = (selectedOption, { action }) => {
        this.setState(
            {
                selected: selectedOption,
                isOptionSelected: true
            },
            () => this.save(action)
        );
    };

    save(action) {
        if(this.state.selected) {
            document.getElementById(this.props.reference).value = this.state.selected.value;
        } else {
            // Clear selected data
            document.getElementById(this.props.reference).value = '';
        }
    }

    render() {
        return (
            <div>
                <Select
                    components={AnimatedComponents}
                    isClearable={this.state.clearable}
                    closeMenuOnSelect={true}
                    isDisabled={this.state.disabled}
                    defaultValue={this.state.default}
                    onChange={this.handleChange}
                    options={this.state.options}
                />
                {this.state.message && this.state.isOptionSelected && (
                    // Available types: https://getbootstrap.com/docs/5.0/utilities/colors/#colors
                    <div className={this.state.message.type}>{this.state.message.content}</div>
                )}
            </div>
        );
    }
}
