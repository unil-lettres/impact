import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

export default class MultiUserSelect extends Component {
    constructor(props){
        super(props);
        let data = JSON.parse(this.props.data);
        let allUsers = data.select;
        let defaultUsers = data.default;

        this.state = {
            users: [],
            default: [],
            selected: null,
        };

        this.selectItems = this.handleChange.bind(this);

        Object.keys(allUsers).forEach(key=>{
            this.state.users.push({ value: allUsers[key].id, label: allUsers[key].name});
        });

        Object.keys(defaultUsers).forEach(key=>{
            this.state.default.push({ value: defaultUsers[key].id, label: defaultUsers[key].name});
        });
    }

    componentDidMount() {
        this.save(this.state.default);
    }

    handleChange(selectedOptions) {
        this.setState(
            {
                selected: selectedOptions
            },
            () => this.save()
        );
    };

    save(data = this.state.selected){
        let value = null;
        if(data) {
            value = JSON.stringify(
                data.map( user =>
                    user.value
                )
            );
        }

        document.getElementById(this.props.reference).value = value;
    }


    render() {
        return (
            <Select
                closeMenuOnSelect={ false }
                components={ animatedComponents }
                isMulti
                defaultValue={ this.state.default }
                onChange={ this.selectItems }
                options={ this.state.users }
            />
        );
    }
}

const elementId = 'rct-multi-user-select';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    let ref = document.getElementById(elementId).getAttribute('ref');
    ReactDOM.render(<MultiUserSelect data={ data } reference={ ref } />, document.getElementById(elementId));
}
