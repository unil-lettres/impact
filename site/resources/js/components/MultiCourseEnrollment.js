import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";

class MultiCourseTeacherEnrollment extends MultiSelect {
    select = (record, option) => {
        return axios.post(
            '/enrollments',
            {
                'course_id': option.value,
                'user_id': record.id,
                'role': 'teacher',
            },
        );
    }

    remove = (record, option) => {
        return axios.delete(
            '/enrollments',
            {
                data: {
                    'course_id': option.value,
                    'user_id': record.id,
                    'role': 'teacher',
                },
            },
        );
    }
}

class MultiCourseStudentEnrollment extends MultiSelect {
    select = (record, option) => {
        return axios.post(
            '/enrollments',
            {
                'course_id': option.value,
                'user_id': record.id,
                'role': 'student',
            },
        );
    }

    remove = (record, option) => {
        return axios.delete(
            '/enrollments',
            {
                data: {
                    'course_id': option.value,
                    'user_id': record.id,
                    'role': 'student',
                },
            },
        );
    }
}
const elementIdSdt = 'rct-multi-course-teacher-select';
if (document.getElementById(elementIdSdt)) {
    const root = createRoot(document.getElementById(elementIdSdt));

    let data = document.getElementById(elementIdSdt).getAttribute('data');
    root.render(<MultiCourseTeacherEnrollment data={ data } />);
}

const elementIdThr = 'rct-multi-course-student-select';
if (document.getElementById(elementIdThr)) {
    const root = createRoot(document.getElementById(elementIdThr));

    let data = document.getElementById(elementIdThr).getAttribute('data');
    root.render(<MultiCourseStudentEnrollment data={ data } />);
}
