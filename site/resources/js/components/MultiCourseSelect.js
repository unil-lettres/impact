import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from "axios";
import Select from "react-select";
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

export default class MultiCourseSelect extends Component {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            record: data.record,
            role: data.role,
            options: [],
            defaults: [],
            selected: [],
        };

        Object.keys(data.options).forEach(key=>{
            this.state.options.push({
                value: data.options[key].id,
                label: data.options[key].name
            });
        });

        Object.keys(data.defaults).forEach(key=>{
            this.state.defaults.push({
                value: data.defaults[key].id,
                label: data.defaults[key].name
            });
        });

        this.state.selected = this.state.defaults;
    }

    handleChange = (selectedOptions, { action }) => {
        let added = _.differenceWith(selectedOptions, this.state.selected, _.isEqual).map( course =>
            course.value
        );
        let removed = _.differenceWith(this.state.selected, selectedOptions, _.isEqual).map( course =>
            course.value
        );

        this.setState(
            {
                selected: selectedOptions
            },
            () => this.save(added, removed, action)
        );
    };

    save(added, removed, action) {
        if (added && added.length > 0) {
            this.createEnrollment(added[0], action);
        } else if(removed && removed.length > 0) {
            this.deleteEnrollment(removed[0], action);
        } else {
            console.log('nothing to add or delete');
        }
    }

    createEnrollment(course, action) {
        axios.post('/enrollments', {
            user: this.state.record.id,
            role: this.state.role,
            course: course
        }).then(response => {
            console.log(response);
        }).catch(error => {
            console.log(error)
        });
    }

    deleteEnrollment(course, action) {
        this.findEnrollment(course).then(function (response) {
            if(response != null && response.data.hasOwnProperty("enrollment")) {
                axios.delete("/enrollments/" + response.data.enrollment.id)
                .then(function (response) {
                    console.log(response);
                }).catch(function (error) {
                    console.log(error);
                });
            }
        }).catch(function (error) {
            console.log(error);
        });
    }

    findEnrollment(course) {
        return axios.get('/enrollments/find', {
            params: {
                user: this.state.record.id,
                course: course,
                role: this.state.role
            }
        }).then(function (response) {
            return response;
        }).catch(function (error) {
            console.log(error);
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
                isDisabled={ this.state.record.admin }
                defaultValue={ this.state.defaults }
                onChange={ this.handleChange }
                options={ this.state.options }
            />
        );
    }
}

const elementIdSdt = 'rct-multi-course-teacher-select';
if (document.getElementById(elementIdSdt)) {
    let data = document.getElementById(elementIdSdt).getAttribute('data');
    ReactDOM.render(<MultiCourseSelect data={ data } />, document.getElementById(elementIdSdt));
}

const elementIdThr = 'rct-multi-course-student-select';
if (document.getElementById(elementIdThr)) {
    let data = document.getElementById(elementIdThr).getAttribute('data');
    ReactDOM.render(<MultiCourseSelect data={ data } />, document.getElementById(elementIdThr));
}
