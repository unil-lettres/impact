import React from 'react';
import ReactDOM from 'react-dom';
import MultiEnrollmentSelect from "./MultiEnrollmentSelect";

export default class MultiCourseEnrollment extends MultiEnrollmentSelect {
    constructor(props){
        super(props);
    }
}

const elementIdSdt = 'rct-multi-course-teacher-select';
if (document.getElementById(elementIdSdt)) {
    let data = document.getElementById(elementIdSdt).getAttribute('data');
    ReactDOM.render(<MultiCourseEnrollment data={ data } context={ 'user' } />, document.getElementById(elementIdSdt));
}

const elementIdThr = 'rct-multi-course-student-select';
if (document.getElementById(elementIdThr)) {
    let data = document.getElementById(elementIdThr).getAttribute('data');
    ReactDOM.render(<MultiCourseEnrollment data={ data } context={ 'user' } />, document.getElementById(elementIdThr));
}
