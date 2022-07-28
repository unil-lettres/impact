import React from 'react';
import { createRoot } from "react-dom/client";

import MultiEnrollmentSelect from "./MultiEnrollmentSelect";

export default class MultiUserEnrollment extends MultiEnrollmentSelect {
    constructor(props){
        super(props);
    }
}

const elementIdSdt = 'rct-multi-user-teacher-select';
if (document.getElementById(elementIdSdt)) {
    const root = createRoot(document.getElementById(elementIdSdt));

    let data = document.getElementById(elementIdSdt).getAttribute('data');
    root.render(<MultiUserEnrollment data={ data } context={ 'course' } />);
}

const elementIdThr = 'rct-multi-user-student-select';
if (document.getElementById(elementIdThr)) {
    const root = createRoot(document.getElementById(elementIdThr));

    let data = document.getElementById(elementIdThr).getAttribute('data');
    root.render(<MultiUserEnrollment data={ data } context={ 'course' } />);
}
