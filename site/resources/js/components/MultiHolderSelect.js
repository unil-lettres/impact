import React from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import MultiSelect from "./MultiSelect";
import _ from "lodash";

class MultiHolderSelect extends MultiSelect {
    constructor(props) {
        super(props);

        const data = JSON.parse(this.props.data);

        // Override options & values state properties to
        // add an "isFixed" or "isExpired" property when needed.
        const isValidityExpired = (validity) => validity && new Date(validity) < new Date();

        this.state = {
            ...this.state,
            options: _.map(
                data.options,
                option => ({
                    value: option.id,
                    label: option.name,
                    ...(isValidityExpired(option.validity) ? { isExpired: true } : {}),
                })
            ),
            values: _.map(
                data.defaults,
                option => ({
                    value: option.id,
                    label: option.name,
                    ...(isValidityExpired(option.validity) ? { isExpired: true } : {}),
                })
            ),
        };
    }

    select = (record, option) => {
        return axios.put(
            '/enrollments/attach',
            {'card_id': record.id, 'user_id': option.value},
        ).catch(error => {
            if (error?.response?.data?.type) {
                console.log(error.response.data.type);
                this.printError(error.response.data.message);
            }

            return Promise.reject(error);
        });
    }

    remove = (record, option) => {
        return axios.put(
            '/enrollments/detach',
            {'card_id': record.id, 'user_id': option.value},
        ).catch(error => {
            if (error?.response?.data?.type) {
                console.log(error.response.data.type);
                this.printError(error.response.data.message);
            }

            return Promise.reject(error);
        });
    }
}

const elementId = 'rct-multi-holder-select';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<MultiHolderSelect data={ data } />);
}
