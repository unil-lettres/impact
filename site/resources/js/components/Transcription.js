import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import ContentEditable from 'react-contenteditable'
import axios from "axios";
import sanitizeHtml from 'sanitize-html';

class Line {
    constructor(number, speaker, speech) {
        this.number = number;
        this.speaker = speaker;
        this.speech = speech;
    }

    toJSON() {
        return {
            number: this.number,
            speaker: this.speaker,
            speech: this.speech
        };
    }
}

export default class Transcription extends Component {
    constructor (props) {
        super(props)

        const data = JSON.parse(this.props.data);
        const transcription = data.card.box2.data ?? [];
        const disabled = data.disabled ?? true;
        this.contentRef = React.createRef();

        this.state = {
            original: Object.assign([], _.cloneDeep(transcription)),
            lines: Object.assign([], _.cloneDeep(transcription)),
            editable: !disabled
        };

        this.edit = this.edit.bind(this);
        this.cancel = this.cancel.bind(this);
        this.addRow = this.addRow.bind(this);
        this.hideActions = this.hideActions.bind(this);
        this.showActions = this.showActions.bind(this);
        this.deleteLine = this.deleteLine.bind(this);
        this.deleteTranscription = this.deleteTranscription.bind(this);

        this.initVariables(data);
        this.updateButton();
    }

    initVariables(data) {
        this.editButton = document.getElementById('edit-' + this.props.reference);
        this.cancelButton = document.getElementById('cancel-' + this.props.reference);
        this.card = data.card;
        this.version = data.card.box2.version;
        this.editorId = 'rct-transcription';
        this.editorErrorMsgId = 'edit-failed-' + this.props.reference;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
    }

    componentDidMount() {
        if(this.editButton) {
            this.editButton.addEventListener('click', this.edit, false);
        }

        if(this.cancelButton) {
            this.cancelButton.addEventListener('click', this.cancel, false);
        }
    }

    updateButton() {
        let editor = document.getElementById(this.editorId);
        let editorErrorMsgId = document.getElementById(this.editorErrorMsgId);

        if(this.editButton) {
            switch (this.state.editable) {
                case true:
                    editorErrorMsgId.classList.add('d-none');
                    editor.classList.add('editing');
                    this.editButton.classList.remove("btn-primary");
                    this.editButton.classList.add('btn-success');
                    this.editButton.innerText = this.saveLabel;
                    this.cancelButton.classList.remove("d-none");

                    // If transcription is empty, then add the first line
                    if(!this.validate(this.state.lines)) {
                        this.addRow()
                    }
                    break;
                case false:
                default:
                    editor.classList.remove("editing");
                    this.editButton.classList.remove("btn-success");
                    this.editButton.classList.add('btn-primary');
                    this.editButton.textContent = this.editLabel;
                    this.cancelButton.classList.add("d-none");
            }
        }
    }

    edit() {
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

        this.updateButton();
    }

    cancel() {
        if(this.state.editable) {
            this.setState({
                    // We restore the transcription initially loaded from the db
                    // We use cloneDeep to avoid a reference
                    lines: _.cloneDeep(this.state.original),
                    // We disable the edition mode
                    editable: false
                },
                () => this.updateButton()
            )
        }
    }

    save() {
        const lines = this.validate(this.state.lines) ? this.state.lines : null;

        axios.put('/cards/' + this.card.id + '/transcription', {
            transcription: lines,
            box: this.props.reference
        }).then(response => {
            console.log(response);
            this.setState({
                // We copy the saved content to the original state
                // We use cloneDeep to avoid a reference
                original: _.cloneDeep(this.state.lines)
            });
        }).catch(error => {
            console.log(error);
            // Display an error message to the user
            document.getElementById(this.editorErrorMsgId)
                .classList
                .remove("d-none");
        });
    }

    validate(lines) {
        // Not valid if transcription is not an array or is empty
        if (!Array.isArray(lines) || !lines.length) {
            return false;
        }

        // Valid otherwise
        return true;
    }

    sanitize(html) {
        return sanitizeHtml(html, {
            allowedTags: [ 'br' ]
        });
    }

    addRow() {
        // TODO: create new line on keypress (enter)

        this.setState({
                lines: [
                    ...this.state.lines,
                    new Line(
                        "",
                        "",
                        ""
                    ).toJSON()
                ]
            },
            () => this.fixNumbers()
        );
    }

    fixNumbers() {
        this.state.lines.forEach(function (row, index) {
            row.number = index + 1;
        });

        this.setState({
            lines: this.state.lines
        });
    }

    deleteTranscription() {
        this.setState({
            lines: [
                new Line(
                    "1",
                    "",
                    ""
                ).toJSON()
            ]
        });
    }

    showActions = index => (event) => {
        if(this.state.editable) {
            const actions = document.getElementById('actions-'+index);

            if (actions.hasChildNodes()) {
                let children = actions.childNodes;

                for (let i = 0; i < children.length; i++) {
                    children[i].classList.remove("d-none");
                }
            }
        }
    }

    hideActions = index => (event) => {
        if(this.state.editable) {
            const actions = document.getElementById('actions-'+index);

            if (actions.hasChildNodes()) {
                let children = actions.childNodes;

                for (let i = 0; i < children.length; i++) {
                    children[i].classList.add("d-none");
                }
            }
        }
    }

    deleteLine = index => (event) => {
        if(this.state.lines[index]) {
            // Remove line at specific index
            this.state.lines.splice(index, 1)

            this.setState({
                    lines: this.state.lines
                },
                () => this.fixNumbers()
            );
        }
    }

    handleChange = params => (event) => {
        if(this.state.lines[params.index]) {
            switch (params.column) {
                case "speaker":
                    this.state.lines[params.index].speaker = this.sanitize(event.target.value);
                    break;
                case "speech":
                    this.state.lines[params.index].speech = this.sanitize(event.target.value);
                    break;
            }
        }
    };

    render () {
        return (
            <div className="editor">
                {this.state.editable &&
                    <input  type="submit" className="button mb-2 mr-2" onClick={ this.addRow } value="Add Line" />
                }
                {this.state.editable &&
                    <input  type="submit" className="button mb-2" onClick={ this.deleteTranscription } value="Delete text" />
                }
                <table>
                    <tbody ref={ this.contentRef }>
                        {
                            this.state.lines.map((line, index) => (
                                <tr key={ index }
                                    onMouseEnter={ this.showActions(index) }
                                    onMouseLeave={ this.hideActions(index) } >
                                    <td id={'line-'+index} className="line pr-2 align-top">
                                        { line.number }
                                    </td>
                                    <td id={'speaker-'+index} className="speaker pr-2 align-top">
                                        <ContentEditable
                                            html={ line.speaker ?? "" }
                                            tagName="span"
                                            disabled={ !this.state.editable }
                                            onChange={ this.handleChange({"index": index, "column": "speaker"}) }
                                        />
                                    </td>
                                    <td id={'speech-'+index} className="speech align-top">
                                        <ContentEditable
                                            html={ line.speech ?? "" }
                                            tagName="span"
                                            disabled={ !this.state.editable }
                                            onChange={ this.handleChange({"index": index, "column": "speech"}) }
                                        />
                                    </td>
                                    <td id={'actions-'+index} className="actions">
                                        <span className="action d-none" onClick={ this.deleteLine(index) }>
                                            <i className="far fa-times-circle"></i>
                                        </span>
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
