import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";

class MultiUserTeacherEnrollment extends MultiSelect {
    select = (record, option) => {
        return axios.post(
            '/enrollments',
            {
                'course_id': record.id,
                'user_id': option.value,
                'role': 'teacher',
            },
        );
    }

    remove = (record, option) => {
        return axios.delete(
            '/enrollments',
            {
                data: {
                    'course_id': record.id,
                    'user_id': option.value,
                    'role': 'teacher',
                },
            },
        );
    }
}

class MultiUserStudentEnrollment extends MultiSelect {
    select = (record, option) => {
        return axios.post(
            '/enrollments',
            {
                'course_id': record.id,
                'user_id': option.value,
                'role': 'student',
            },
        );
    }

    remove = (record, option) => {
        return axios.delete(
            '/enrollments',
            {
                data: {
                    'course_id': record.id,
                    'user_id': option.value,
                    'role': 'student',
                },
            },
        );
    }
}

const elementIdSdt = 'rct-multi-user-teacher-select';
if (document.getElementById(elementIdSdt)) {
    const root = createRoot(document.getElementById(elementIdSdt));

    let data = document.getElementById(elementIdSdt).getAttribute('data');
    root.render(<MultiUserTeacherEnrollment data={ data } />);
}

const elementIdThr = 'rct-multi-user-student-select';
if (document.getElementById(elementIdThr)) {
    const root = createRoot(document.getElementById(elementIdThr));

    let data = document.getElementById(elementIdThr).getAttribute('data');
    root.render(<MultiUserStudentEnrollment data={ data } />);
}
