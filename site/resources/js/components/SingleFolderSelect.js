import React from 'react';
import { createRoot } from "react-dom/client";

import SingleSelect from "./SingleSelect";

export default class SingleFolderSelect extends SingleSelect {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        Object.keys(data.options).forEach(key=>{
            if(data.options[key]) {
                this.state.options.push({
                    value: data.options[key].id,
                    label: data.options[key].title
                });
            }
        });

        if(data.default) {
            this.state.default.push({
                value: data.default.id,
                label: data.default.title
            });
        }
    }
}

const elementId = 'rct-single-folder-select';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    root.render(<SingleFolderSelect data={ data } reference={ reference } />);
}
