import React, { Component } from 'react';
import { createRoot } from "react-dom/client";
import { flushSync } from "react-dom";

import ContentEditable from 'react-contenteditable'
import axios from "axios";
import sanitizeHtml from 'sanitize-html';
import _ from "lodash";

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
        const transcription = data.card.box2.icor ?? [];
        const disabled = data.disabled ?? true;
        this.contentRef = React.createRef();

        this.state = {
            original: _.cloneDeep(transcription),
            lines: _.cloneDeep(transcription),
            editable: !disabled
        };

        this.edit = this.edit.bind(this);
        this.cancel = this.cancel.bind(this);
        this.addRow = this.addRow.bind(this);
        this.hideActions = this.hideActions.bind(this);
        this.showActions = this.showActions.bind(this);
        this.deleteLine = this.deleteLine.bind(this);
        this.toggleNumber = this.toggleNumber.bind(this);
        this.deleteTranscription = this.deleteTranscription.bind(this);
        this.import = this.import.bind(this);
        this.export = this.export.bind(this);
        this.handleKeyDown = this.handleKeyDown.bind(this);

        this.initVariables(data);
        this.updateUi();
    }

    initVariables(data) {
        this.editButton = document.getElementById('edit-' + this.props.reference);
        this.cancelButton = document.getElementById('cancel-' + this.props.reference);
        this.importOpenModalButton = document.getElementById('import-' + this.props.reference);
        this.exportButton = document.getElementById('export-' + this.props.reference);
        this.deleteButton = document.getElementById('clear-' + this.props.reference);
        this.syncButton = document.getElementById('sync-' + this.props.reference);
        this.hideButton = document.getElementById('hide-' + this.props.reference);
        this.card = data.card;
        this.version = data.card.box2.version;
        this.editorId = 'rct-transcription';
        this.editorErrorMsgId = 'edit-failed-' + this.props.reference;
        this.editorEmptyTranscriptionMsgId = 'empty-' + this.props.reference;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
        this.cancelLabel = data.cancelLabel ?? 'Cancel';
        this.deleteLineActionLabel = data.deleteLineActionLabel ?? 'Delete the line';
        this.toggleNumberActionLabel = data.toggleNumberActionLabel ?? 'Visibility of the numbering';
        this.importModalTitleLabel = data.importModalTitleLabel ?? 'Import a transcription';
        this.importModalHelpLabel = data.importModalHelpLabel ?? 'Paste a transcription into the text box below. Please respect the ICOR transcription conventions to preserve your layout.';
    }

    componentDidMount() {
        if(this.editButton) {
            this.editButton.addEventListener('click', this.edit, false);
        }

        if(this.cancelButton) {
            this.cancelButton.addEventListener('click', this.cancel, false);
        }

        if(this.importOpenModalButton) {
            this.importButton = document.getElementById('import-transcription');
            this.importContent = document.getElementById('import-transcription-content');
            this.importModal = document.getElementById('importModal');

            this.importButton.addEventListener('click', this.import, false);
            this.importContent.onkeydown = function(e) {
                if (e.key === 'Tab') { // Block to catch when tab key is pressed
                    e.preventDefault(); // Prevent default action

                    // Get textarea
                    let textarea = e.target;

                    // Get cursor position
                    let start = textarea.selectionStart;
                    let end = textarea.selectionEnd;

                    // Set textarea value to: text before cursor + tab + text after cursor
                    textarea.value = textarea.value.substring(0, start)
                        + "\t"
                        + textarea.value.substring(end);

                    // Put cursor to right of inserted tab
                    textarea.selectionStart = textarea.selectionEnd = start + 1;
                }
            };
        }

        if(this.exportButton) {
            this.exportButton.addEventListener('click', this.export, false);
        }

        if(this.deleteButton) {
            this.deleteButton.addEventListener('click', this.deleteTranscription, false);
        }
    }

    updateUi() {
        let editor = document.getElementById(this.editorId);
        let editorErrorMsg = document.getElementById(this.editorErrorMsgId);
        let editorEmptyTranscriptionMsg = document.getElementById(this.editorEmptyTranscriptionMsgId);

        if(this.editButton) {
            switch (this.state.editable) {
                case true:
                    editorErrorMsg.classList.add('d-none');
                    editorEmptyTranscriptionMsg.classList.add('d-none');
                    editor.classList.add('editing');
                    this.editButton.classList.remove("btn-primary");
                    this.editButton.classList.add('btn-success');
                    this.editButton.innerText = this.saveLabel;
                    this.cancelButton.classList.remove("d-none");
                    this.deleteButton.classList.remove("d-none");
                    this.importOpenModalButton.classList.add("d-none");
                    this.exportButton.classList.add("d-none");
                    this.syncButton?.classList.add("d-none");
                    this.hideButton?.classList.add("d-none");

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
                    this.deleteButton.classList.add("d-none");
                    this.importOpenModalButton.classList.remove("d-none");
                    this.exportButton.classList.remove("d-none");
                    this.syncButton?.classList.remove("d-none");
                    this.hideButton?.classList.remove("d-none");

                    // If transcription is empty, then add the empty message
                    if(!this.validate(this.state.lines)) {
                        editorEmptyTranscriptionMsg.classList.remove('d-none');
                    }
            }
        }
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

    isLastRow(index) {
        return this.state.lines.length === index + 1
    }

    fixNumbers() {
        let i = 1;
        this.state.lines.forEach(function (row) {
            // Fix the row number if is present or is an empty string
            if(row.number || row.number === "") {
                row.number = i++;
            }
        });

        this.setState({
            lines: this.state.lines
        });
    }

    edit() {
        switch (!this.state.editable) {
            case false:
                flushSync(() => {
                    this.setState({
                        editable: false
                    });
                });

                this.save();
                break;
            case true:
            default:
                flushSync(() => {
                    this.setState({
                        editable: true
                    });
                });
        }

        this.updateUi();
    }

    cancel() {
        if(this.state.editable) {
            flushSync(() => {
                this.setState({
                        // We restore the transcription initially loaded from the db
                        // We use cloneDeep to avoid a reference
                        lines: _.cloneDeep(this.state.original),
                        // We disable the edition mode
                        editable: false
                    },
                    () => this.updateUi()
                );
            });
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

    addRow() {
        this.state.lines = [
            ...this.state.lines,
            new Line(
                "",
                "",
                ""
            ).toJSON()
        ]

        // Fix the number of each row
        this.fixNumbers();
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

            // Fix the number of each row
            this.fixNumbers();
        }
    }

    toggleNumber = index => (event) => {
        if(this.state.lines[index]) {
            // Remove or add a number to the row
            this.state.lines[index].number = this.state.lines[index].number ? null : "";

            // Fix the number of each row
            this.fixNumbers();
        }
    }

    import(event) {
        if(this.importContent.value !== "") {
            let textareaValue = this.importContent.value;

            // Split the textarea value by newline to get an array of lines
            let lines = textareaValue.split('\n');

            // Initialize an empty array to hold the parsed text
            let parsedText = [];

            // Iterate over each line
            for (let line of lines) {
                // Split the line by tab to get an array of tab-separated values
                let tabSeparatedValues = line.split('\t');

                // Add the array of tab-separated values to the main array
                parsedText.push(tabSeparatedValues);
            }

            // Now, parsedText is an array where each line is an element and each tab-separated value is a sub-array
            console.log(parsedText);

            // TODO: transform array (line, speaker, speech) & save the transcription
        }

        // Close the modal
        let bootstrapModal = bootstrap.Modal.getInstance(this.importModal);
        bootstrapModal.hide();
    }

    export(event) {
        // Get the export format from the button attribute
        const format = event.currentTarget
            .getAttribute('format');

        axios({
            method: 'post',
            url: '/cards/' + this.card.id + '/export',
            data: {
                box: this.props.reference,
                format: format
            },
            responseType: 'blob'
        }).then(response => {
            // Trigger the download of the response data
            let fileURL = window.URL.createObjectURL(new Blob([response.data]));
            let fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', this.card.title + '.' + format);
            document.body.appendChild(fileLink);
            fileLink.click();
        }).catch(error => {
            console.log(error);
        });
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

            this.setState({
                lines: this.state.lines
            });
        }
    };

    handleKeyDown = index => (event) => {
        if(event.key === 'Tab' && this.isLastRow(index) ) {
            this.addRow();
        }
    }

    render () {
        return (
            <div>
                <div id="transcription-content">
                    <table>
                        <tbody ref={ this.contentRef }>
                            {
                                this.state.lines.map((line, index) => (
                                    <tr key={ index }
                                        onMouseEnter={ this.showActions(index) }
                                        onMouseLeave={ this.hideActions(index) } >
                                        <td id={'line-'+index} className="line pe-2 align-top">
                                            { line.number }
                                        </td>
                                        <ContentEditable
                                            id={'speaker-'+index}
                                            className="speaker pe-2 align-top"
                                            html={ line.speaker ?? "" }
                                            tagName="td"
                                            disabled={ !this.state.editable }
                                            onChange={ this.handleChange({"index": index, "column": "speaker"}) }
                                        />
                                        <ContentEditable
                                            id={'speech-'+index}
                                            className="speech align-top"
                                            html={ line.speech ?? "" }
                                            tagName="td"
                                            disabled={ !this.state.editable }
                                            onChange={ this.handleChange({"index": index, "column": "speech"}) }
                                            onKeyDown={ this.handleKeyDown(index) }
                                        />
                                        <td id={'actions-'+index} className="actions">
                                            <span className="action delete-line me-1 d-none"
                                                  onClick={ this.deleteLine(index) }
                                                  title={ this.deleteLineActionLabel }>
                                                <i className="far fa-times-circle"/>
                                            </span>
                                            <span className="action delete-number d-none"
                                                  onClick={ this.toggleNumber(index) }
                                                  title={ this.toggleNumberActionLabel }>
                                                <i className={`far ${line.number ? "fa-minus-square" : "fa-plus-square"}`}/>
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            }
                        </tbody>
                    </table>
                </div>

                {/* Import transcription modal */}
                <div className="modal fade" id="importModal" tabIndex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div className="modal-dialog modal-lg">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title" id="importModalLabel">{ this.importModalTitleLabel }</h5>
                                <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div className="modal-body">
                                <div className="mb-2">
                                    { this.importModalHelpLabel }
                                </div>
                                <textarea className="form-control col-md-12"
                                          name="import-transcription-content"
                                          id="import-transcription-content"
                                          rows="20"></textarea>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">{ this.cancelLabel }</button>
                                <button type="button" className="btn btn-primary" id="import-transcription">{ this.saveLabel }</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

const elementId = 'rct-transcription';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    let reference = document.getElementById(elementId).getAttribute('reference');
    root.render(<Transcription data={ data } reference={ reference } />);
}
