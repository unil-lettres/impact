import React, { Component, useEffect, useRef } from 'react';
import { createRoot } from "react-dom/client";

import axios from "axios";
import _ from "lodash";

function SpeakerInput(props) {
    const inputRef = useRef(null);

    return (
        <div className="speaker" onClick={ () => inputRef.current.focus()}>
            <input type="text" ref={inputRef} {...props} />
        </div>
    );
}

function SpeechInput(props) {
    const inputRef = useRef(null);

    useEffect(() => {
        // Adjust the height related to the content.
        inputRef.current.style.height = 0;
        inputRef.current.style.height = inputRef.current.scrollHeight + 'px';
    }, [props.value]);

    return <textarea
        ref={inputRef}
        className="speech"
        style={{width: `${Transcription.MAX_CARACTERS_SPEECH}ch`}}
        {...props}
    />;
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
    }

    initVariables(data) {
        this.editButton = document.getElementById('edit-' + this.props.reference);
        this.cancelButton = document.getElementById('cancel-' + this.props.reference);
        this.importOpenModalButton = document.getElementById('import-' + this.props.reference);
        this.exportButton = document.getElementById('export-' + this.props.reference);
        this.deleteButton = document.getElementById('clear-' + this.props.reference);
        this.syncButton = document.getElementById('sync-' + this.props.reference);
        this.hideButton = document.getElementById('hide-' + this.props.reference);
        this.editorEmptyTranscriptionMsg = document.getElementById('empty-' + this.props.reference);
        this.editor = document.getElementById('rct-transcription');
        this.editorErrorMsg = document.getElementById('edit-failed-' + this.props.reference);
        this.card = data.card;
        this.version = data.card.box2.version;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
        this.cancelLabel = data.cancelLabel ?? 'Cancel';
        this.deleteLineActionLabel = data.deleteLineActionLabel ?? 'Delete the line';
        this.toggleNumberActionLabel = data.toggleNumberActionLabel ?? 'Visibility of the numbering';
        this.importModalTitleLabel = data.importModalTitleLabel ?? 'Import a transcription';
        this.importModalHelpLabel = data.importModalHelpLabel ?? 'Paste a transcription into the text box below. Please respect the ICOR transcription conventions to preserve your layout.';
        this.lineToFocusOnUpdate = null;
        this.caretPositionOnUpdate = null;
    }

    componentDidMount() {
        if(this.editButton) {
            this.editButton.addEventListener(
                'click',
                () => this.handleEditButtonClick(),
            );
        }

        if(this.cancelButton) {
            this.cancelButton.addEventListener(
                'click',
                () => this.handleCancelButtonClick(),
            );
        }

        if(this.exportButton) {
            this.exportButton.addEventListener(
                'click',
                () => this.handleExportClick(),
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

        this.componentDidMountOrUpdate();
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

        this.componentDidMountOrUpdate();
    }

    componentDidMountOrUpdate() {

        // If transcription is empty, then display an empty message.
        this.editorEmptyTranscriptionMsg.classList.toggle(
            'd-none',
            !this.shouldDisplayNoTranscriptionLabel(
                this.state.editable,
                this.state.lines,
            ),
        );

        this.editor?.classList.toggle('editing', this.state.editable);
        this.cancelButton?.classList.toggle('d-none', !this.state.editable);
        this.deleteButton?.classList.toggle('d-none', !this.state.editable);
        this.importOpenModalButton?.classList.toggle('d-none', this.state.editable);
        this.exportButton?.classList.toggle('d-none', this.state.editable);
        this.syncButton?.classList.toggle('d-none', this.state.editable);
        this.hideButton?.classList.toggle('d-none', this.state.editable);

        if (this.editButton) {
            this.editButton.classList.toggle('btn-primary', !this.state.editable);
            this.editButton.classList.toggle('btn-success', this.state.editable);
            this.editButton.innerText = this.state.editable ? this.saveLabel : this.editLabel;
        }
    }

    handleEditButtonClick() {

        // If the user is not in edit mode, we enter it.
        // Otherwise, we save the transcription.
        if (this.state.editable) {
            this.setState({ editable: false });
            this.save();
        } else {
            let newState = { editable: true };

            // Add the first line if the transcription is empty.
            if(this.state.lines.length === 0) {
                newState.lines = this.addRow(this.state.lines);
            }

            this.setState({ ...newState });
        }
    }

    handleCancelButtonClick() {
        this.setState({
            // We restore the transcription initially loaded from the db
            lines: _.cloneDeep(this.state.original),

            editable: false
        });
    }

    save() {
        axios.put('/cards/' + this.card.id + '/transcription', {
            transcription: this.state.lines,
            box: this.props.reference
        }).then(response => {
            console.log(response);
            this.setState({
                // We copy the saved content to the original state
                original: _.cloneDeep(this.state.lines)
            });
        }).catch(error => {
            console.log(error);
            // Display an error message to the user
            this.editorErrorMsg
                .classList
                .remove("d-none");
        });
    }

    handleToggleNumberClick = index => {
        // TODO make working for section
        // Remove or add a number to the row
        const newLines = this.state.lines.map((row, i) => {
            if (i === index) {
                return { ...row, number: row.number ? null : i + 1 };
            } else {
                return row;
            }
        });

        this.setState({ lines: this.fixNumbers(newLines) });
    }

    handleImportClick() {
        if(this.state.importContentValue) {
            this.enterEditMode();

            let lines = this.state.importContentValue.split('\n');

            const importedLines = lines.map((line, index) => {
                const tabSeparatedValues = line.split('\t');
                let [speaker, speech] = (
                    tabSeparatedValues.length > 1
                    ? tabSeparatedValues
                    : ["", tabSeparatedValues[0]]
                );

                return new Line(
                    index + 1,
                    speaker.substring(0, 3),
                    speech,
                ).toJSON();
            });

            this.setState({ lines: importedLines });
        }
    }

    handleExportClick(event) {
        const format = event.currentTarget.getAttribute('format');

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

    handleImportContentKeyDown(event) {
        if (event.keyCode === Transcription.KEY_TAB) {
            event.preventDefault();

            // Override tab behavior to insert a tab character instead of
            // changing the focus.

            let textarea = event.target;

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

    handleSpeakerChange(index, event) {
        const speaker = event.target.value.substring(0, 3);

        const newLines = this.state.lines.map((row, i) => {
            if (i === index) {
                return { ...row, speaker: speaker };
            } else {
                return row;
            }
        });

        // This is to avoid the cursor being put at the end of the input.
        this.lineToFocusOnUpdate = document.activeElement;
        this.caretPositionOnUpdate = document.activeElement.selectionStart;

        this.setState({ lines: newLines });
    }

    handleSpeechChange(index, event) {
        this.setState({
            lines: this.updateSectionSpeech(
                this.state.lines,
                index,
                event.target.value,
            ),
        });

        // This is to avoid the cursor being put at the end of the input.
        this.lineToFocusOnUpdate = document.activeElement;
        this.caretPositionOnUpdate = document.activeElement.selectionStart;
    }

    handleSpeechKeyDown(index, event) {
        switch (event.keyCode) {
            case Transcription.KEY_ENTER:
                event.preventDefault();

                // Split the current line in two parts: before and after the caret.
                const speech = event.target.value ?? "";
                const caretPosition = event.target.selectionStart;
                const textBeforeCaret = speech.substring(0, caretPosition);
                const textAfterCaret = speech.substring(caretPosition);
                const columnToFocus = textAfterCaret === "" ? "speaker" : "speech";

                // Update the current section with the text before the caret.
                let newLines = this.updateSectionSpeech(
                    this.state.lines,
                    index,
                    textBeforeCaret,
                );

                // Add a new section with the text after the caret.
                const newSectionIndex = this.getNextSectionIndex(newLines, index);

                newLines = this.addRow(
                    newLines,
                    newSectionIndex,
                    "",
                    textAfterCaret,
                    false,
                );

                if (textAfterCaret.length > 0) {
                    newLines = this.updateSectionSpeech(
                        newLines,
                        newSectionIndex,
                        textAfterCaret,
                    );
                }

                this.lineToFocusOnUpdate = `${columnToFocus}-${newSectionIndex}`;
                this.caretPositionOnUpdate = 0;

                this.setState({ lines: newLines });

                break;

            case Transcription.KEY_TAB:
                // Check shift key to avoid creating a new line if the tab
                // traversal is backwards.
                if (!event.shiftKey && this.isLastRow(this.state.lines, index)) {
                    this.setState({
                        lines: this.addRow(this.state.lines, null, "", ""),
                    });
                }
                break;

            case Transcription.KEY_BACKSPACE:
                if (this.shouldMergeWithPrevious(index, event)) {
                    event.preventDefault();

                    const previousIndex = this.getPreviousSectionIndex(
                        this.state.lines,
                        index,
                    );
                    this.setState({
                        lines: this.mergeLines(
                            this.state.lines,
                            previousIndex,
                            index,
                        ),
                    });
                }
                break;

            case Transcription.KEY_DEL:

                if (this.shouldMergeWithNext(this.state.lines, index, event)) {
                    event.preventDefault();

                    const nextIndex = this.getNextSectionIndex(
                        this.state.lines,
                        index,
                    );
                    this.setState({
                        lines: this.mergeLines(
                            this.state.lines,
                            index,
                            nextIndex,
                        ),
                    })
                }
                break;
        }
    }

    handleSpeakerKeyDown(index, event) {

        switch (event.keyCode) {
            case Transcription.KEY_ENTER:
                event.preventDefault();

                const caretPosition = event.target.selectionStart;
                const speaker = this.state.lines[index].speaker ?? "";
                const textBeforeCaret = speaker.substring(0, caretPosition);
                const textAfterCaret = speaker.substring(caretPosition);
                const speechOnNewLine = this.state.lines[index].speech ?? "";

                // Remove the text after the caret from current line and the
                // speech (they will be moved to the new line).
                const newLines = this.state.lines.map((row, i) => {
                    if (i === index) {
                        return { ...row, speaker: textBeforeCaret, speech: '' };
                    } else {
                        return row;
                    }
                });

                // Create a new line with the text after the caret and with the
                // speech from the previous line.
                this.lineToFocusOnUpdate = `speaker-${index + 1}`;

                this.setState({
                    lines: this.addRow(
                        newLines,
                        index + 1,
                        textAfterCaret,
                        speechOnNewLine,
                    )
                });
                break;

            case Transcription.KEY_BACKSPACE:
                if (this.shouldMergeWithPrevious(index, event)) {
                    event.preventDefault();

                    const previousIndex = this.getPreviousSectionIndex(
                        this.state.lines,
                        index,
                    );
                    this.setState({
                        lines: this.mergeLines(
                            this.state.lines,
                            previousIndex,
                            index,
                        ),
                    });
                }
                break;
        }
    }

    isLastRow(lines, index) {
        return lines.length === this.getNextSectionIndex(lines, index);
    }

    /**
     * @param {Array} lines An array of lines.
     * @returns an array of the given lines with fixed numbers.
     */
    fixNumbers(lines) {
        let i = 1;

        return lines.map(function (row) {
            // Fix the row number if is present or is an empty string
            if(row.number || row.number === "") {
                return {...row, number: i++};
            } else {
                return row;
            }
        });
    }

    /**
     * Return an array with the given line added.
     *
     * @param {Array} lines An array of lines to add a new row to.
     * @param {int} index The index to add the new row at. If null, the row will be added at the end.
     * @param {string} speaker The speaker column.
     * @param {string} speech The speech column.
     * @param {boolean} linkedToPrevious If the row is linked to the previous row (meanin it's in the same section).
     */
    addRow(lines, index = null, speaker = "", speech = "", linkedToPrevious = false) {

        // If index is not specified, add at the end.
        if (index === null) {
            index = lines.length;
        }

        const newLines = [
            ...lines.slice(0, index),
            new Line("", speaker, speech, linkedToPrevious).toJSON(),
            ...lines.slice(index)
        ];

        return this.fixNumbers(newLines);
    }

    getNextSectionIndex(lines, index) {
        while (lines[index + 1]?.linkedToPrevious ?? false) {
            index++;
        }
        return index + 1;
    }

    getPreviousSectionIndex(lines, index) {
        while (lines[index - 1]?.linkedToPrevious ?? false) {
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
        this.lineToFocusOnUpdate = 'speaker-0';
    }

    /**
     * @param {int} index The index to update the section.
     * @param {string} speech The new speech for this section.
     * @returns an array with the given section updated.
     */
    updateSectionSpeech(lines, index, speech) {
        // Some browser add a newline in specific situations (like when the user
        // press space after a line break). We remove all newline characters to
        // homogenize the behavior accross browsers.
        let remainingLine = speech.replace(/\n/g, '');

        let first = index, current = first;
        const speaker = lines[first].speaker;

        // We remove the involved lines in the state to recreate them.
        let newLines = this.removeSection(lines, current);

        if (remainingLine.length === 0) {
            newLines = this.addRow(newLines, current, speaker);
        }

        // We split the value into multiple lines of max caracters length.
        while (remainingLine.length > 0) {

            // Whitespaces are allowed to be the "MAX + 1" caracter of a line.
            // It allow to terminate a word exactly at "MAX" caracters.
            const endWithWhitespace = remainingLine[
                Transcription.MAX_CARACTERS_SPEECH
            ]?.match(/\s/g);

            let line = remainingLine.slice(
                0,
                Transcription.MAX_CARACTERS_SPEECH + (endWithWhitespace ? 1 : 0 ),
            );

            if (remainingLine.length > Transcription.MAX_CARACTERS_SPEECH) {

                const lastSpace = this.lastIndexOfWhiteSpace(line);
                const lastHyphen = line.lastIndexOf('-');
                const lastBreak = Math.max(lastSpace, lastHyphen);

                if (lastBreak > -1) {
                    line = line.slice(0, lastBreak + 1);
                    remainingLine = remainingLine.slice(lastBreak + 1);
                } else {
                    line = line.slice(0, Transcription.MAX_CARACTERS_SPEECH);
                    remainingLine = remainingLine.slice(Transcription.MAX_CARACTERS_SPEECH);
                }

                // If the first character on the new line is a space, we remove
                // all subsequent whitespaces. Multiple whitespaces are not
                // displayed in the textarea when they are on a line break, but
                // are still present in the "value".
                // To keep the data consistent with how the textarea display the
                // text, we remove them.
                remainingLine = remainingLine.replace(/^\s+/, '');
            } else {
                remainingLine = '';
            }

            if (current === first) {
                newLines = this.addRow(newLines, current, speaker, line, false);
            } else {
                newLines = this.addRow(newLines, current, '', line, true);
            }

            current++;
        }

        return this.fixNumbers(newLines);
    }

    /**
     * Return an array with the second section merged with the first one.
     * The second section will be deleted.
     *
     * @param {int} firstSectionIndex The first section index to append the second one.
     * @param {int} secondSectionIndex The second section index.
     */
    mergeLines(lines, firstSectionIndex, secondSectionIndex) {

        const firstSection = this.getSectionAtIndex(lines, firstSectionIndex);
        const secondSection = this.getSectionAtIndex(lines, secondSectionIndex);
        let newLines = this.removeSection(lines, firstSectionIndex);

        newLines = this.updateSectionSpeech(
            newLines,
            firstSection.index,
            `${firstSection.speech ?? ""}${secondSection.speech ?? ""}`,
        );

        this.lineToFocusOnUpdate = `speech-${firstSection.index}`;
        this.caretPositionOnUpdate = (firstSection.speech ?? "").length;

        // Update the speaker of the first section if it is empty.
        return newLines.map((line, i) => {
            if (firstSectionIndex === i) {
                return {
                    ...line,
                    speaker: firstSection.speaker || secondSection.speaker || "",
                };
            } else {
                return line;
            }
        });
    }

    /**
     * @param {Array} lines Array of lines to search in.
     * @param {int} index The index to search the section.
     * @returns An object representing the section at the specified index.
     */
    getSectionAtIndex(lines, index) {
        return this.getAggregatedLines(lines).find((line) => line.index === index);
    }

    /**
     * Remove section at the specified index from the given lines.
     *
     * @param {Array} lines An array of lines.
     * @param {int} index The index of the section to remove.
     * @returns An array with the section removed.
     */
    removeSection(lines, index) {
        if (lines[index].linkedToPrevious) {
            throw new Error("Unable to remove linked lines. The specified line must be the first of a group.");
        }

        const end = this.getNextSectionIndex(lines, index);
        const newLines = [
            ...lines.slice(0, index),
            ...lines.slice(end),
        ];

        return this.fixNumbers(newLines);
    }

    shouldMergeWithPrevious(index, event) {
        let should = true;

        // Must not be on the first line.
        should &= index > 0;

        // Must have pressed backspace when the carret is at position 0.
        should &= event.target.selectionStart === 0 && event.target.selectionEnd === 0;

        return should;
    }

    shouldMergeWithNext(lines, index, event) {
        let should = true;

        // Must not be on the last line.
        should &= !this.isLastRow(lines, index);

        // Must have pressed delete when the carret is at the last position.
        const length = event.target.value.length;
        should &= event.target.selectionStart === length && event.target.selectionEnd === length;

        return should;
    }

    /**
     * Return an array of lines to be used in speech.
     * These lines represents sections.
     *
     * @param {Array} lines Array of lines.
     * @returns {Array} An array of lines representing sections.
     */
    getAggregatedLines(lines) {
        const aggregatedLines = [];

        lines.forEach((line, index) => {
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
    lastIndexOfWhiteSpace(line) {
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
    getHtmlLinesNumber(section) {
        return Array
            .from({length: section.linesNumber})
            .map((_, i) => ( <div key={i}>{parseInt(section.number, 10) + i}</div> ));
    }

    shouldDisplayNoTranscriptionLabel(editable, lines) {
        return !editable && lines.length <= 1 && !lines[0]?.speech && !lines[0]?.speaker;
    }

    render() {

        if (this.shouldDisplayNoTranscriptionLabel(
            this.state.editable,
            this.state.lines,
        )) {
            return;
        }

        return (
            <div>
                <div id="transcription-content">
                    {
                        this.getAggregatedLines(this.state.lines).map((section) => (
                            <div
                                key={ section.index }
                                className="transcription-row d-flex align-items-stretch"
                            >
                                <div className="line-number">
                                    { this.getHtmlLinesNumber(section) }
                                </div>
                                <SpeakerInput
                                    id={`speaker-${section.index}`}
                                    value={ section.speaker ?? "" }
                                    disabled={ !this.state.editable }
                                    onChange={ event => this.handleSpeakerChange(section.index, event) }
                                    onKeyDown={ event => this.handleSpeakerKeyDown(section.index, event) }
                                />
                                <SpeechInput
                                    id={`speech-${section.index}`}
                                    disabled={ !this.state.editable }
                                    onChange={ event => this.handleSpeechChange(section.index, event) }
                                    onKeyDown={ event => this.handleSpeechKeyDown(section.index, event) }
                                    value={ section.speech ?? "" }
                                />
                                <div className="transcription-actions">
                                     <span
                                        className="me-1 d-none"
                                        onClick={ () => this.setState({
                                            lines: this.removeSection(this.state.lines)
                                        })}
                                        title={ this.deleteLineActionLabel }
                                    >
                                         <i className="far fa-times-circle"/>
                                    </span>
                                    <span
                                        className="d-none"
                                        onClick={ () => this.handleToggleNumberClick(section.index) }
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
                                    onClick={ () => this.handleImportClick()}
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
