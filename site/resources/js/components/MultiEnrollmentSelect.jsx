import React from 'react';
import { createRoot } from "react-dom/client";

import MultiSelect from "./MultiSelect";
import _ from "lodash";

class MultiEnrollmentSelect extends MultiSelect {
    constructor(props) {
        super(props);

        const data = JSON.parse(this.props.data);

        this.role = data.role;

        this.state = {
            ...this.state,
            values: _.map(data.defaults, this.mapOption),
        };
    }

    /**
     * Override options & values state properties to
     * add an "isFixed" or "isExpired" property when needed.
     */
    mapOption = (opt) => ({
        value: opt.id,
        label: opt.name,
        isExpired: opt.validity && new Date(opt.validity) < new Date(),
        isFixed: this.props.context === 'course' && opt.type === 'external',
    });

    /**
     * Both selects (courses for a user, or users for a course) lazily
     * load their options from the server, since either list can be huge.
     */
    isAsync = () => true;

    loadOptions = (inputValue) => {
        const url = {
            'course': `/users/${this.state.record.id}/courses/search`,
            'user': `/courses/${this.state.record.id}/users/search`,
        }[this.props.context];

        return axios.get(url, {
            params: { q: inputValue },
        }).then((response) => _.map(
            response.data.courses ?? response.data.users,
            this.mapOption
        ));
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
