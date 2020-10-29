import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import ContentEditable from 'react-contenteditable'
import axios from "axios";

export default class Transcription extends Component {
    constructor (props) {
        super(props)

        let data = JSON.parse(this.props.data);
        this.contentRef = React.createRef();

        this.state = {
            lines: data.card.box2.data ?? [],
            editable: false
        };

        this.edit = this.edit.bind(this);
        this.addLine = this.addLine.bind(this);

        this.initVariables(data);
        this.updateButton(this.disabled);
    }

    initVariables(data) {
        this.editButtonId = 'edit-' + this.props.reference;
        this.button = document.getElementById(this.editButtonId);
        this.card = data.card;
        this.version = data.card.box2.version;
        this.disabled = data.disabled ?? true;
        this.editorId = 'rct-transcription';
        this.editorErrorMsgId = 'edit-failed-' + this.props.reference;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
    }

    componentDidMount() {
        if(this.button) {
            this.button.addEventListener('click', this.edit, false);
        }
    }

    updateButton(isReadOnly) {
        let editor = document.getElementById(this.editorId);
        let editorErrorMsgId = document.getElementById(this.editorErrorMsgId);

        if(this.button) {
            switch (isReadOnly) {
                case true:
                    editor.classList.remove("editing");
                    this.button.classList.remove("btn-success");
                    this.button.classList.add('btn-primary');
                    this.button.textContent = this.editLabel;
                    break;
                case false:
                default:
                    editorErrorMsgId.classList.add('d-none');
                    editor.classList.add('editing');
                    this.button.classList.remove("btn-primary");
                    this.button.classList.add('btn-success');
                    this.button.innerText = this.saveLabel;
            }
        }
    }

    edit(e){
        switch (!this.state.editable) {
            case false:
                this.setState({
                    editable: false
                })

                this.save();
                break;
            case true:
            default:
                this.setState({
                    editable: true
                })
        }

        this.updateButton(!this.state.editable);
    }

    validate(lines) {
        // Not valid if transcription is not an array or is empty
        if (!Array.isArray(lines) || !lines.length) {
            return false;
        }

        // Valid otherwise
        return true;
    }

    save() {
        const lines = this.validate(this.state.lines) ? this.state.lines : null;

        axios.put('/cards/' + this.card.id + '/transcription', {
            transcription: lines,
            box: this.props.reference
        }).then(response => {
            console.log(response);
        }).catch(error => {
            console.log(error);
            // Display an error message to the user
            document.getElementById(this.editorErrorMsgId)
                .classList
                .remove("d-none");
        });
    }

    addLine() {
        // TODO: create new line on keypress (enter)

        const number = this.state.lines.length ? this.state.lines.length + 1 : 1;

        const newLine = {
            "number" : number.toString(),
            "speaker" : "",
            "speech" : ""
        }

        this.setState({
            lines: [...this.state.lines, newLine]
        })
    }

    handleChange = params => (event) => {
        if(this.state.lines[params.index]) {
            switch (params.column) {
                case "speaker":
                    this.state.lines[params.index].speaker = event.target.value;
                    break;
                case "speech":
                    this.state.lines[params.index].speech = event.target.value;
                    break;
            }
        }
    };

    render () {
        return (
            <div className="editor">
                {this.state.editable &&
                <input  type="submit" className="button mb-2" onClick={ this.addLine } value="Add Line" />
                }
                <table>
                    <tbody ref={ this.contentRef }>
                        {
                            this.state.lines.map((line, index) => (
                                <tr key={ index }>
                                    <td className="line pr-2 align-top">
                                        { line.number }
                                    </td>
                                    <td className="speaker pr-2 align-top">
                                        <ContentEditable
                                            html={ line.speaker ?? "" }
                                            tagName="span"
                                            disabled={ !this.state.editable }
                                            onChange={ this.handleChange({"index": index, "column": "speaker"}) }
                                        />
                                    </td>
                                    <td className="speech align-top">
                                        <ContentEditable
                                            html={ line.speech ?? "" }
                                            tagName="span"
                                            disabled={ !this.state.editable }
                                            onChange={ this.handleChange({"index": index, "column": "speech"}) }
                                        />
                                    </td>
                                </tr>
                            ))
                        }
                    </tbody>
                </table>
            </div>
        );
    }
}

const elementId = 'rct-transcription';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    ReactDOM.render(<Transcription data={ data } reference={ reference } />, document.getElementById(elementId));
}
