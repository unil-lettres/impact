import React from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import MultiSelect from "./MultiSelect";


export default class MultiTagSelect extends MultiSelect {

    select = (record, option) => {
        return axios.put(`/tags/${option.value}/attach/${record.id}`);
    }

    remove = (record, option) => {
        return axios.put(`/tags/${option.value}/detach/${record.id}`);
    }

    create = (record, name) => {
        return axios.post(`/tags/create`, { name, card_id: record.id })
    }
}

const element = document.getElementById('rct-multi-tag-select');
if (element) {
    const root = createRoot(element);

    let data = element.getAttribute('data');
    let createLabel = element.getAttribute('createLabel');
    root.render(<MultiTagSelect data={data} canCreate={true} createLabel={createLabel} />);
}
