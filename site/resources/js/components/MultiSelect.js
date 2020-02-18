import React, { Component } from 'react';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

export default class MultiSelect extends Component {
    constructor(props){
        super(props);

        this.state = {
            records: this.props.records,
            default: this.props.default,
            selected: null,
        };

        this.selectItems = this.handleChange.bind(this);
    }

    componentDidMount() {
        this.save(this.state.default);
    }

    handleChange(selectedOptions) {
        this.setState(
            {
                selected: selectedOptions
            },
            () => this.save()
        );
    };

    save(data = this.state.selected){
        let value = [];
        if(data) {
            value = data.map( record =>
                record.value
            )
        }

        document.getElementById(this.props.reference).value = JSON.stringify(value);
    }


    render() {
        return (
            <Select
                closeMenuOnSelect={ false }
                components={ animatedComponents }
                isMulti
                defaultValue={ this.state.default }
                onChange={ this.selectItems }
                options={ this.state.records }
            />
        );
    }
}
