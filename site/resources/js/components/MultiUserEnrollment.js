import React from 'react';
import ReactDOM from 'react-dom';
import MultiEnrollmentSelect from "./MultiEnrollmentSelect";

export default class MultiUserEnrollment extends MultiEnrollmentSelect {
    constructor(props){
        super(props);
    }
}

const elementIdSdt = 'rct-multi-user-teacher-select';
if (document.getElementById(elementIdSdt)) {
    let data = document.getElementById(elementIdSdt).getAttribute('data');
    ReactDOM.render(<MultiUserEnrollment data={ data } context={ 'course' } />, document.getElementById(elementIdSdt));
}

const elementIdThr = 'rct-multi-user-student-select';
if (document.getElementById(elementIdThr)) {
    let data = document.getElementById(elementIdThr).getAttribute('data');
    ReactDOM.render(<MultiUserEnrollment data={ data } context={ 'course' } />, document.getElementById(elementIdThr));
}
