import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Select from "react-select";
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

export default class SingleFolderSelect extends Component {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            options: [],
            default: [],
            selected: [],
        };

        Object.keys(data.options).forEach(key=>{
            this.state.options.push({
                value: data.options[key].id,
                label: data.options[key].title
            });
        });

        if(data.default) {
            this.state.default.push({
                value: data.default.id,
                label: data.default.title
            });
        }

        this.state.selected = this.state.default;
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
                isClearable={ true }
                closeMenuOnSelect={ true }
                defaultValue={ this.state.default }
                onChange={ this.handleChange }
                options={ this.state.options }
            />
        );
    }
}

const elementId = 'rct-single-folder-select';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    ReactDOM.render(<SingleFolderSelect data={ data } reference={ reference } />, document.getElementById(elementId));
}
