import React from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import MultiSelect from "./MultiSelect";

export default class MultiEditorSelect extends MultiSelect {

    select = (record, option) => {
        return axios.put(
            '/enrollments/attach',
            {'card_id': record.id, 'user_id': option.value},
        );
    }

    remove = (record, option) => {
        return axios.put(
            '/enrollments/detach',
            {'card_id': record.id, 'user_id': option.value},
        );
    }
}

const elementId = 'rct-multi-editor-select';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<MultiEditorSelect data={ data } />);
}
