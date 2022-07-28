import React, { Component } from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import Select from "react-select";
import makeAnimated from 'react-select/animated';
import _ from "lodash";

const animatedComponents = makeAnimated();

export default class MultiEditorSelect extends Component {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            record: data.record,
            options: [],
            defaults: [],
            selected: [],
        };

        Object.keys(data.options).forEach(key=>{
            if(data.options[key]) {
                this.state.options.push({
                    value: data.options[key].id,
                    label: data.options[key].name
                });
            }
        });

        Object.keys(data.defaults).forEach(key=>{
            if(data.defaults[key]) {
                this.state.defaults.push({
                    value: data.defaults[key].id,
                    label: data.defaults[key].name
                });
            }
        });

        this.state.selected = this.state.defaults;
    }

    handleChange = (selectedOptions, { action }) => {
        let added = _.differenceWith(selectedOptions, this.state.selected, _.isEqual).map( user =>
            user.value
        );
        let removed = _.differenceWith(this.state.selected, selectedOptions, _.isEqual).map( user =>
            user.value
        );

        this.setState(
            {
                selected: selectedOptions
            },
            () => this.save(added, removed, action)
        );
    };

    save(added, removed, action) {
        axios.put('/enrollments/cards', {
            course: this.state.record.course.id,
            card: this.state.record.id,
            add: added,
            remove: removed,
            action: action
        }).then(response => {
            console.log(response);
        }).catch(error => {
            console.log(error)
        });
    }

    render() {
        return (
            <Select
                isMulti
                components={ animatedComponents }
                isClearable={ false }
                closeMenuOnSelect={ false }
                escapeClearsValue={ false }
                backspaceRemovesValue={ false }
                defaultValue={ this.state.defaults }
                onChange={ this.handleChange }
                options={ this.state.options }
            />
        );
    }
}

const elementId = 'rct-multi-editor-select';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<MultiEditorSelect data={ data } />);
}
