import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";
import _ from "lodash";

class MultiEnrollmentSelect extends MultiSelect {
    constructor(props) {
        super(props);

        const data = JSON.parse(this.props.data);

        this.role = data.role;

        // Override options & values state properties to
        // add an "isFixed" or "isExpired" property when needed.
        const isValidityExpired = (validity) => validity && new Date(validity) < new Date();
        const isFixed = (option) => {
            if (this.props.context === 'course') {
                return option.type === 'external';
            }
            return false;
        };

        this.state = {
            ...this.state,
            options: _.map(
                data.options,
                option => ({
                    value: option.id,
                    label: option.name,
                    ...(isFixed(option) ? { isFixed: true } : {}),
                    ...(isValidityExpired(option.validity) ? { isExpired: true } : {}),
                })
            ),
            values: _.map(
                data.defaults,
                option => ({
                    value: option.id,
                    label: option.name,
                    ...(isFixed(option) ? { isFixed: true } : {}),
                    ...(isValidityExpired(option.validity) ? { isExpired: true } : {}),
                })
            ),
        };
    }

    select = (record, option) => {
        const [course_id, user_id] = {
            'course': [option.value, record.id],
            'user': [record.id, option.value],
        }[this.props.context];

        return axios.post(
            '/enrollments',
            { course_id, user_id, 'role': this.role },
        ).catch(error => {
            if (error?.response?.data?.type) {
                console.log(error.response.data.type);
                this.printError(error.response.data.message);
            }

            return Promise.reject(error);
        });
    }

    remove = (record, option) => {
        const [course_id, user_id] = {
            'course': [option.value, record.id],
            'user': [record.id, option.value],
        }[this.props.context];

        return axios.delete(
            '/enrollments',
            { data: { course_id, user_id, 'role': this.role } },
        ).catch(error => {
            if (error?.response?.data?.type) {
                console.log(error.response.data.type);
                this.printError(error.response.data.message);
            }

            return Promise.reject(error);
        });
    }
}

const elementIdCourseThr = 'rct-multi-course-manager-select';
if (document.getElementById(elementIdCourseThr)) {
    const root = createRoot(document.getElementById(elementIdCourseThr));

    let data = document.getElementById(elementIdCourseThr).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='course' />);
}

const elementIdCourseSdt = 'rct-multi-course-member-select';
if (document.getElementById(elementIdCourseSdt)) {
    const root = createRoot(document.getElementById(elementIdCourseSdt));

    let data = document.getElementById(elementIdCourseSdt).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='course' />);
}

const elementIdUserThr = 'rct-multi-user-manager-select';
if (document.getElementById(elementIdUserThr)) {
    const root = createRoot(document.getElementById(elementIdUserThr));

    let data = document.getElementById(elementIdUserThr).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='user' />);
}

const elementIdUserStd = 'rct-multi-user-member-select';
if (document.getElementById(elementIdUserStd)) {
    const root = createRoot(document.getElementById(elementIdUserStd));

    let data = document.getElementById(elementIdUserStd).getAttribute('data');
    root.render(<MultiEnrollmentSelect data={ data } context='user' />);
}
