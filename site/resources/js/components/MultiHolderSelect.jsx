import React from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import MultiSelect from "./MultiSelect";

export default class MultiHolderSelect extends MultiSelect {

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
