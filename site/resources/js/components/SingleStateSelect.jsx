import React from 'react';
import { createRoot } from "react-dom/client";

import SingleSelect from "./SingleSelect";

export default class SingleStateSelect extends SingleSelect {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        Object.keys(data.options).forEach(key=>{
            if(data.options[key]) {
                this.state.options.push({
                    value: data.options[key].id,
                    label: data.options[key].name
                });
            }
        });

        if(data.default) {
            this.state.default.push({
                value: data.default.id,
                label: data.default.name
            });
        } else {
            // If a state is not set, set default
            // to first option if available
            this.state.default.push({
                value: data.options[0]?.id,
                label: data.options[0]?.name
            });
        }
    }
}

const elementId = 'rct-single-state-select';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    root.render(<SingleStateSelect data={ data } reference={ reference } />);
}
