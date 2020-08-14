import React from 'react';
import ReactDOM from 'react-dom';
import SingleSelect from "./SingleSelect";

export default class SingleFileSelect extends SingleSelect {
    constructor(props){
        super(props);

        let data = JSON.parse(this.props.data);

        Object.keys(data.options).forEach(key=>{
            this.state.options.push({
                value: data.options[key].id,
                label: data.options[key].name
            });
        });

        if(data.default) {
            this.state.default.push({
                value: data.default.id,
                label: data.default.name
            });
        }
    }
}

const elementId = 'rct-single-file-select';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    ReactDOM.render(<SingleFileSelect data={ data } reference={ reference } />, document.getElementById(elementId));
}
