import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";

class MultiEnrollmentSelect extends MultiSelect {
    constructor(props) {
        super(props);

        const data = JSON.parse(this.props.data);
        this.role = data.role;
    }

    select = (record, option) => {
        const [course_id, user_id] = {
            'course': [option.value, record.id],
            'user': [record.id, option.value],
        }[this.props.context];

        return axios.post(
            '/enrollments',
            { course_id, user_id, 'role': this.role },
        );
    }

    remove = (record, option) => {
        const [course_id, user_id] = {
            'course': [option.value, record.id],
            'user': [record.id, option.value],
        }[this.props.context];

        return axios.delete(
            '/enrollments',
            { data: { course_id, user_id, 'role': this.role } },
        );
    }
}

const elementIdCourseThr = 'rct-multi-course-teacher-select';
if (document.getElementById(elementIdCourseThr)) {
    const root = createRoot(document.getElementById(elementIdCourseThr));

    let data = document.getElementById(elementIdCourseThr).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='course' />);
}

const elementIdCourseSdt = 'rct-multi-course-student-select';
if (document.getElementById(elementIdCourseSdt)) {
    const root = createRoot(document.getElementById(elementIdCourseSdt));

    let data = document.getElementById(elementIdCourseSdt).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='course' />);
}

const elementIdUserThr = 'rct-multi-user-teacher-select';
if (document.getElementById(elementIdUserThr)) {
    const root = createRoot(document.getElementById(elementIdUserThr));

    let data = document.getElementById(elementIdUserThr).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='user' />);
}

const elementIdUserStd = 'rct-multi-user-student-select';
if (document.getElementById(elementIdUserStd)) {
    const root = createRoot(document.getElementById(elementIdUserStd));

    let data = document.getElementById(elementIdUserStd).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='user' />);
}
