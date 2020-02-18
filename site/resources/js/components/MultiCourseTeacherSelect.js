import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import MultiSelect from './MultiSelect';

export default class MultiCourseTeacherSelect extends Component {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            records: [],
            default: [],
            reference: this.props.reference,
        };

        Object.keys(data.options).forEach(key=>{
            this.state.records.push({ value: data.options[key].id, label: data.options[key].name});
        });

        Object.keys(data.default).forEach(key=>{
            this.state.default.push({ value: data.default[key].id, label: data.default[key].name});
        });
    }

    render() {
        return (
            <MultiSelect
                records={ this.state.records }
                default={ this.state.default }
                reference={ this.state.reference }
            />
        );
    }
}

const elementId = 'rct-multi-course-teacher-select';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    let ref = document.getElementById(elementId).getAttribute('ref');
    ReactDOM.render(<MultiCourseTeacherSelect data={ data } reference={ ref } />, document.getElementById(elementId));
}
