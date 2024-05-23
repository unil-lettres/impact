import React, { Component, useRef } from 'react';
import { createRoot } from "react-dom/client";
import { flushSync } from "react-dom";

import axios from "axios";
import _ from "lodash";

function SpeakerInput(props) {
    const inputRef = useRef(null);

    return (
        <div className="speaker" onClick={ () => inputRef.current.focus()}>
            <input {...props} ref={inputRef} />
        </div>
    );
}

class Line {

    constructor(number, speaker, speech, linkedToPrevious = false) {
        this.number = number;
        this.speaker = speaker;
        this.speech = speech;
        this.linkedToPrevious = linkedToPrevious;
    }

    toJSON() {
        return {
            number: this.number,
            speaker: this.speaker,
            speech: this.speech,
            linkedToPrevious: this.linkedToPrevious,
        };
    }
}

export default class Transcription extends Component {
    static MAX_CARACTERS_SPEECH = 55;
    static MAX_CARACTERS_LEGACY_SPEECH = 65;
    static KEY_ENTER = 13;
    static KEY_BACKSPACE = 8;
    static KEY_TAB = 9;
    static KEY_DEL = 46;

    constructor (props) {
        super(props)

        const data = JSON.parse(this.props.data);
        const transcription = data.card.box2.icor ?? [];
        const disabled = data.disabled ?? true;

        this.state = {
            original: _.cloneDeep(transcription),
            lines: _.cloneDeep(transcription),
            editable: !disabled,
            importContentValue: '',
        };

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
        this.noTranscriptionLabel = data.noTranscriptionLabel ?? 'No transcription';
        this.lineToFocusOnUpdate = null;
    }

    handleImportContentKeyDown(event) {
        if (event.keyCode === Transcription.KEY_TAB) {
            event.preventDefault();

            let textarea = event.target;

            // Get cursor position.
            let start = textarea.selectionStart;
            let end = textarea.selectionEnd;

            // Set textarea value to: text before cursor + tab + text after cursor.
            textarea.value = textarea.value.substring(0, start)
                + "\t"
                + textarea.value.substring(end);

            // Put cursor to right of inserted tab.
            textarea.selectionStart = textarea.selectionEnd = start + 1;
        }
    }

    componentDidMount() {
        if(this.editButton) {
            this.editButton.addEventListener('click', () => this.edit(), false);
        }

        if(this.cancelButton) {
            this.cancelButton.addEventListener('click', () => this.cancel(), false);
        }

        if(this.exportButton) {
            this.exportButton.addEventListener(
                'click',
                () => this.export(),
                false,
            );
        }

        if(this.deleteButton) {
            this.deleteButton.addEventListener(
                'click',
                () => this.deleteTranscription(),
                false,
            );
        }
    }

    componentDidUpdate() {
        if(this.lineToFocusOnUpdate) {

            let line;
            if (typeof this.lineToFocusOnUpdate === 'string') {
                line = document.getElementById(this.lineToFocusOnUpdate);
            } else {
                line = this.lineToFocusOnUpdate;
            }
            line.focus();
            line.selectionStart = this.caretPositionOnUpdate ?? 0;
            line.selectionEnd = this.caretPositionOnUpdate ?? 0;
            this.lineToFocusOnUpdate = this.caretPositionOnUpdate = null;
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

    isLastRow(index) {
        return this.state.lines.length === this._getNextSectionIndex(index);
    }

    _fixNumbers() {
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

    addRow(index = null, speaker = "", speech = "", linkedToPrevious = false) {
        if (index === null) {
            index = this.state.lines.length;
        }

        this.state.lines.splice(
            index,
            0,
            new Line("", speaker, speech, linkedToPrevious).toJSON()
        );

        this._fixNumbers();
    }

    /**
     * Add a section after the section specified by the given line index.
     *
     * @param {int} index the line index to add a section after
     * @param {string} speaker the speaker column
     * @param {string} speech the speech column
     * @returns The index of the first line of the new section.
     */
    addSectionAfter(index, speaker = "", speech = "") {
        const newSectionIndex = this._getNextSectionIndex(index);

        this.addRow(newSectionIndex, speaker, speech, false);

        if (speech.length > 0) {
            this.setSectionSpeech(newSectionIndex, speech);
        }

        return newSectionIndex;
    }

    _getNextSectionIndex(index) {
        while (this.state.lines[index + 1]?.linkedToPrevious ?? false) {
            index++;
        }
        return index + 1;
    }

    _getPreviousSection(index) {
        while (this.state.lines[index - 1]?.linkedToPrevious ?? false) {
            index--;
        }
        return index - 1;
    }

    deleteTranscription() {
        this.setState({
            lines: [
                new Line(
                    "1",
                    "",
                    "",
                    0
                ).toJSON()
            ]
        });
        this.lineToFocusOnUpdate = 'speaker-1';
    }

    toggleNumber = index => {
        if(this.state.lines[index]) {
            // Remove or add a number to the row
            this.state.lines[index].number = this.state.lines[index].number ? null : "";

            this._fixNumbers();
        }
    }

    import() {
        if(this.state.importContentValue) {
            // Toggle the edition mode
            this.edit();

            // Reset the lines to avoid a first empty line
            this.state.lines = [];

            // Get the textarea value
            let textareaValue = this.state.importContentValue;

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

            // Now, parsedText is an array where each line is an element
            // and each tab-separated value is a sub-array. We can now
            // iterate over it and create a new Line object for each line.
            parsedText.forEach((line, index) => {
                // If the line has more than one element, then the first
                // element is the speaker and the second is the speech. Otherwise,
                // the speaker is empty and the only element is the speech.
                let [speaker, speech] = line.length > 1 ? line : ["", line[0]];

                this.state.lines = [
                    ...this.state.lines,
                    new Line(index + 1, speaker, speech).toJSON()
                ]
            });

            // Set the state with the imported lines
            this.setState({
                lines: this.state.lines
            });
        }
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

    handleSpeakerChange(index, event) {
        const speaker = event.target.value.substring(0, 3);

        this.state.lines[index].speaker = speaker;
        this.setState({ lines: this.state.lines });
    }

    setSectionSpeech(index, value) {
        // Some browser add a newline in specific situations (like when the user
        // press space after a line break). We remove all newline characters to
        // homogenize the behavior accross browsers.
        let remainingLine = value.replace(/\n/g, ' ');

        let first = index, current = first;
        const speaker = this.state.lines[first].speaker;

        // We remove the involved lines in the state to recreate them.
        this._removeLinkedLines(current);

        if (remainingLine.length === 0) {
            this.addRow(current, speaker);
        }

        // We split the value into multiple lines of max caracters length.
        while (remainingLine.length > 0) {

            let line = remainingLine.slice(0, Transcription.MAX_CARACTERS_SPEECH);

            if (remainingLine.length > Transcription.MAX_CARACTERS_SPEECH) {
                const charAtEnd = remainingLine[Transcription.MAX_CARACTERS_SPEECH - 1];
                const charOnNewLine = remainingLine[Transcription.MAX_CARACTERS_SPEECH];

                if (charOnNewLine.match(/\s/g)) {
                    // If the first character on the new line is a space, we remove all subsequent white space.
                    // It is this way because of how textarea works. Multiple white space are not
                    // displayed in the textarea (if they are on a broke line) but are still present in the "value".
                    // To keep the data consistent with how the textarea display the text, we remove them.
                    // Using css properties like white-space or break-word can change this behavior
                    // but are not consistent across browser regarding the size of the caracters.
                    line += ' ';
                    remainingLine = remainingLine.slice(line.length).replace(/^\s+/, '');

                    this.lineToFocusOnUpdate = document.activeElement;
                    this.caretPositionOnUpdate = document.activeElement.selectionStart;

                } else if (charAtEnd.match(/\s/g) || charAtEnd === '-') {
                    remainingLine = remainingLine.slice(line.length);
                } else {
                    // If the last word overflow the max caracters, we cut the
                    // word at the last space or hyphen.

                    const lastSpace = this._lastIndexOfWhiteSpace(line);
                    const lastHyphen = line.lastIndexOf('-');
                    const lastBreak = Math.max(lastSpace, lastHyphen);

                    if (lastBreak > -1) {
                        line = line.slice(0, lastBreak + 1);
                        remainingLine = remainingLine.slice(lastBreak + 1);
                    } else {
                        line = line.slice(0, Transcription.MAX_CARACTERS_SPEECH);
                        remainingLine = remainingLine.slice(Transcription.MAX_CARACTERS_SPEECH);
                    }
                }
            } else {
                remainingLine = '';
            }

            if (current === first) {
                this.addRow(current, speaker, line, false);
            } else {
                this.addRow(current, '', line, true);
            }

            current++;
        }

        this._fixNumbers();
    }

    handleSpeechChange(index, event) {
        this.setSectionSpeech(index, event.target.value);
    }

    handleSpeechKeyDown(index, event) {

        switch (event.keyCode) {
            case Transcription.KEY_ENTER:
                event.preventDefault();

                const speech = event.target.value ?? "";
                const caretPosition = event.target.selectionStart;
                const textBeforeCaret = speech.substring(0, caretPosition);
                const textAfterCaret = speech.substring(caretPosition);
                const columnToFocus = textAfterCaret === "" ? "speaker" : "speech";

                this.setSectionSpeech(index, textBeforeCaret);

                const newSectionIndex = this.addSectionAfter(index, "", textAfterCaret);
                this.lineToFocusOnUpdate = `${columnToFocus}-${newSectionIndex}`;
                this.caretPositionOnUpdate = 0;

                break;

            case Transcription.KEY_TAB:
                // Check shift key to avoid creating a new line if the tab
                // traversal is backwards.
                if (!event.shiftKey && this.isLastRow(index)) {
                    this.addRow(null, "", "");
                }
                break;

            case Transcription.KEY_BACKSPACE:
                this._processBackSpace(index, event);
                break;

            case Transcription.KEY_DEL:

                if (this._shouldMergeWithNext(index, event)) {
                    event.preventDefault();

                    const nextIndex = this._getNextSectionIndex(index);
                    this._processMerge(index, nextIndex);
                }
                break;
        }
    }

    /**
     * Merge the second section with the first one. The second section will be deleted.
     * @param {int} firstIndex The first section index to append the second one.
     * @param {int} secondIndex The second section index.
     */
    _processMerge(firstIndex, secondIndex) {

        const firstSection = this.findSectionByIndex(firstIndex);
        const secondSection = this.findSectionByIndex(secondIndex);
        this._removeLinkedLines(firstIndex);

        this.setSectionSpeech(
            firstSection.index,
            `${firstSection.speech ?? ""}${secondSection.speech ?? ""}`,
        );

        this.lineToFocusOnUpdate = `speech-${firstSection.index}`;
        this.caretPositionOnUpdate = (firstSection.speech ?? "").length;

        this.state.lines[firstIndex].speaker = firstSection.speaker || secondSection.speaker || "";
        this.setState({ lines: this.state.lines });
    }

    handleSpeakerKeyDown(index, event) {

        switch (event.keyCode) {
            case Transcription.KEY_ENTER:
                event.preventDefault();

                const lines = [...this.state.lines];
                const caretPosition = event.target.selectionStart;
                const textBeforeCaret = (lines[index].speaker ?? "").substring(0, caretPosition);
                const textAfterCaret = (lines[index].speaker ?? "").substring(caretPosition);
                const speechOnNewLine = lines[index].speech ?? "";

                // Remove the text after the caret from current line and the
                // speech (they will be moved to the new line).
                lines[index].speaker = textBeforeCaret;
                lines[index].speech = "";

                this.setState({ lines: lines });

                // Create a new line with the text after the caret and with the
                // speech from the previous line.
                this.addRow(
                    index + 1,
                    textAfterCaret,
                    speechOnNewLine,
                );
                this.lineToFocusOnUpdate = `speaker-${index + 1}`;
                break;

            case Transcription.KEY_BACKSPACE:
                this._processBackSpace(index, event);
                break;
        }
    }

    findSectionByIndex(index) {
        return this.getAggregatedLines().find((line) => line.index === index);
    }

    _removeLinkedLines(index) {
        if (this.state.lines[index].linkedToPrevious) {
            throw new Error("Unable to remove linked lines. The specified line must be the first of a group.");
        }

        const range = this._getNextSectionIndex(index) - index;
        this.state.lines.splice(index, range);

        this._fixNumbers();
    }

    _shouldMergeWithPrevious(index, event) {
        let should = true;

        // Must not be on the first line.
        should &= index > 0;

        // Must have pressed backspace when the carret is at position 0.
        should &= event.target.selectionStart === 0 && event.target.selectionEnd === 0;

        return should;
    }

    _shouldMergeWithNext(index, event) {
        let should = true;

        // Must not be on the last line.
        should &= !this.isLastRow(index);

        // Must have pressed delete when the carret is at the last position.
        const length = event.target.value.length;
        should &= event.target.selectionStart === length && event.target.selectionEnd === length;

        return should;
    }

    _processBackSpace(index, event) {

        if (this._shouldMergeWithPrevious(index, event)) {
            event.preventDefault();

            const previousIndex = this._getPreviousSection(index);
            this._processMerge(previousIndex, index);
        }
    }

    /**
     * Prepare the lines to be used in content editable div.
     *
     * @returns {Array}
     */
    getAggregatedLines() {
        const aggregatedLines = [];

        this.state.lines.forEach((line, index) => {
            if (line.linkedToPrevious) {
                const lastLine = aggregatedLines.at(-1);
                lastLine.speech += line.speech;
                lastLine.linesNumber++;
            } else {
                aggregatedLines.push({
                    index: index,
                    number: line.number,
                    speaker: line.speaker,
                    speech: line.speech,
                    linesNumber: 1,
                });
            }
        });
        return aggregatedLines;
    }

    /**
     * @param {object} line
     * @returns the index of the last white space in the given line.
     */
    _lastIndexOfWhiteSpace(line) {
        let regex = /\s/g;
        let correspondances;
        let dernierePosition = -1;

        while ((correspondances = regex.exec(line)) !== null) {
            dernierePosition = correspondances.index;
        }

        return dernierePosition;
    }

    /**
     * Return the html for the lines number.
     * Ex: if the line number 32 need to be wrapped on 3 lines, it will return:
     *  <div>32</div>
     *  <div>33</div>
     *  <div>34</div>
     * @param {object} section
     * @returns the html representation for lines number.
     */
    _getHtmlLinesNumber(section) {
        return Array
            .from({length: section.linesNumber})
            .map((_, i) => ( <div key={i}>{parseInt(section.number, 10) + i}</div> ));
    }

    _noTranscritption() {
        return !this.state.editable && this.state.lines.length <= 1 && !this.state.lines[0].speech && !this.state.lines[0].speaker;
    }

    render () {

        if (this._noTranscritption()) {
            return <div className="text-center">{ this.noTranscriptionLabel }</div>;
        }

        return (
            <div>
                <div id="transcription-content">
                    {
                        this.getAggregatedLines().map((section) => (
                            <div
                                key={ section.index }
                                className="transcription-row d-flex align-items-stretch"
                            >
                                <div className="line-number">
                                    { this._getHtmlLinesNumber(section) }
                                </div>
                                <SpeakerInput
                                    type="text"
                                    id={`speaker-${section.index}`}
                                    value={ section.speaker ?? "" }
                                    rows="1"
                                    disabled={ !this.state.editable }
                                    onChange={ event => this.handleSpeakerChange(section.index, event) }
                                    onKeyDown={ event => this.handleSpeakerKeyDown(section.index, event) }
                                />
                                <textarea
                                    className="speech"
                                    id={`speech-${section.index}`}
                                    disabled={ !this.state.editable }
                                    cols={Transcription.MAX_CARACTERS_SPEECH}
                                    rows={section.linesNumber}
                                    onChange={ event => this.handleSpeechChange(section.index, event) }
                                    onKeyDown={ event => this.handleSpeechKeyDown(section.index, event) }
                                    value={ section.speech ?? "" }
                                ></textarea>
                                <div className="transcription-actions">
                                     <span
                                        className="me-1 d-none"
                                        onClick={ () => this._removeLinkedLines(section.index) }
                                        title={ this.deleteLineActionLabel }
                                    >
                                         <i className="far fa-times-circle"/>
                                    </span>
                                    <span
                                        className="d-none"
                                        onClick={ () => this.toggleNumber(section.index) }
                                        title={ this.toggleNumberActionLabel }
                                    >
                                         <i className={`far ${section.number ? "fa-minus-square" : "fa-plus-square"}`}/>
                                     </span>
                                 </div>
                            </div>
                        ))
                    }
                    <div style={{fontFamily: 'courier', position: 'fixed', top: 0, left: 0, background: 'white', border: '1px solid black'}}>
                    {
                        this.state.lines.map((line, index) => (
                            <div key={ index } className='d-flex gap-1'>
                                <div style={{width: '20px', textAlign: 'left'}}>{ index }</div>
                                <div style={{width: '20px', textAlign: 'left'}}>{ line.number }</div>
                                <div style={{width: '30px', textAlign: 'left'}}>{ line.speaker }</div>
                                <div style={{width: '20px', textAlign: 'left'}}>{ line.speech?.length }</div>
                                <div>{ line.speech ? line.speech.replace(/\s/g, '\u00A0') : '' }</div>
                            </div>
                        ))
                    }
                    </div>
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
                                          value={ this.state.importContentValue }
                                          onChange={ (event) => this.setState({ importContentValue: event.target.value }) }
                                          onKeyDown={ (event) => this.handleImportContentKeyDown(event) }
                                          rows="20"></textarea>
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    { this.cancelLabel }
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-primary"
                                    onClick={ () => this.import()}
                                    data-bs-dismiss="modal"
                                    id="import-transcription"
                                >
                                    { this.saveLabel }
                                </button>
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
