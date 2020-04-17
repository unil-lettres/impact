import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Select from "react-select";
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

export default class SingleParentSelect extends Component {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        console.log(data.options);

        this.state = {
            options: [],
            selected: null,
        };

        Object.keys(data.options).forEach(key=>{
            this.state.options.push({
                value: data.options[key].id,
                label: data.options[key].title
            });
        });
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
        }
    }

    render() {
        return (
            <Select
                components={ animatedComponents }
                isClearable={ false }
                closeMenuOnSelect={ true }
                onChange={ this.handleChange }
                options={ this.state.options }
            />
        );
    }
}

const elementId = 'rct-single-parent-select';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    ReactDOM.render(<SingleParentSelect data={ data } reference={ reference } />, document.getElementById(elementId));
}
