import React, { Component } from 'react';
import Select from "react-select";
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

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
        };

        this.state.selected = this.state.default;
        this.state.clearable = data.clearable ? data.clearable : false;
    }

    handleChange = (selectedOption, { action }) => {
        this.setState(
            {
                selected: selectedOption
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
            <Select
                components={ animatedComponents }
                isClearable={ this.state.clearable }
                closeMenuOnSelect={ true }
                isDisabled={ this.state.disabled }
                defaultValue={ this.state.default }
                onChange={ this.handleChange }
                options={ this.state.options }
            />
        );
    }
}
