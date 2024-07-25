import React, { Component } from 'react';
import { createRoot } from "react-dom/client";
import { flushSync } from 'react-dom';

import axios from "axios";
import _ from "lodash";

import { CKEditor } from '@ckeditor/ckeditor5-react';
import { InlineEditor,
    Essentials,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Highlight,
    BlockQuote,
    ListProperties,
    Link,
    Alignment,
    MediaEmbed,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageInsert,
    ImageResize,
    Base64UploadAdapter,
    FontBackgroundColor,
    FontColor,
    FontSize,
    Table,
    TableToolbar,
    TableCellProperties,
    TableProperties,
    HorizontalLine,
    Paragraph,
    Heading } from 'ckeditor5';
import coreTranslationsFr from 'ckeditor5/translations/fr.js';
import coreTranslationsEn from 'ckeditor5/translations/en.js';

import 'ckeditor5/ckeditor5.css';

const editorConfiguration = {
    plugins: [
        Essentials,
        Bold,
        Italic,
        Underline,
        Heading,
        Strikethrough,
        BlockQuote,
        ListProperties,
        Link,
        Alignment,
        MediaEmbed,
        Image,
        ImageCaption,
        ImageStyle,
        ImageToolbar,
        ImageInsert,
        ImageResize,
        Base64UploadAdapter,
        FontColor,
        FontBackgroundColor,
        FontSize,
        Table,
        TableToolbar,
        TableCellProperties,
        TableProperties,
        Highlight,
        HorizontalLine,
        Paragraph
    ],
    toolbar: [
        "heading",
        "|",
        "fontSize",
        "bold",
        "italic",
        "underline",
        "strikethrough",
        "fontColor",
        "highlight",
        "|",
        "alignment",
        "numberedList",
        "bulletedList",
        "blockQuote",
        "horizontalLine",
        "insertTable",
        "|",
        "insertImage",
        "link",
        "mediaEmbed",
    ],
    image: {
        upload: {
            types: [ 'png', 'jpeg' ],
            panel: {
                items: [ 'insertImageViaUrl' ]
            }
        },
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative'
        ]
    },
    table: {
        contentToolbar: [
            'tableColumn', 'tableRow', 'mergeTableCells',
            'tableProperties', 'tableCellProperties'
        ]
    }
};

export default class Editor extends Component {
    constructor (props) {
        super(props)

        const data = JSON.parse(this.props.data);
        const html = data.content ?? null;
        const disabled = data.disabled ?? true;

        this.state = {
            original: _.cloneDeep(html),
            html: _.cloneDeep(html),
            editable: !disabled
        };

        this.edit = this.edit.bind(this);
        this.cancel = this.cancel.bind(this);
        this.onEditorChange = this.onEditorChange.bind(this);

        this.updateEditorConfiguration(data);
        this.initVariables(data);
        this.updateUi();
    }

    componentDidMount() {
        if(this.editButton) {
            this.editButton.addEventListener('click', this.edit, false);
        }

        if(this.cancelButton) {
            this.cancelButton.addEventListener('click', this.cancel, false);
        }
    }

    componentDidUpdate() {
        window.editors = {
            ...(window.editors || []),
            [this.props.reference]: this.state.editable,
        };
    }

    updateEditorConfiguration(data) {
        editorConfiguration.translations = data.locale === 'fr' ? coreTranslationsFr : coreTranslationsEn;
        editorConfiguration.placeholder = data.placeholder ?? '';
    }

    initVariables(data) {
        this.editor = InlineEditor;
        this.cardId = data.cardId;
        this.editButtonId = 'edit-' + this.props.reference;
        this.editButton = document.getElementById(this.editButtonId);
        this.cancelButtonId = 'cancel-' + this.props.reference;
        this.cancelButton = document.getElementById(this.cancelButtonId);
        this.hideButton = document.getElementById('hide-' + this.props.reference);
        this.editorId = 'rct-editor-' + this.props.reference;
        this.editorErrorMsgId = 'edit-failed-' + this.props.reference;
        this.editorEmptyTranscriptionMsgId = 'empty-' + this.props.reference;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
    }

    updateUi() {
        let editor = document.getElementById(this.editorId);
        let editorErrorMsgId = document.getElementById(this.editorErrorMsgId);
        let editorEmptyTranscriptionMsg = document.getElementById(this.editorEmptyTranscriptionMsgId);

        if(this.editButton) {
            switch (this.state.editable) {
                case true:
                    editorErrorMsgId.classList.add('d-none');
                    editorEmptyTranscriptionMsg.classList.add('d-none');
                    editor.classList.add('editing');
                    this.editButton.classList.remove("btn-primary");
                    this.editButton.classList.add('btn-success');
                    this.editButton.innerText = this.saveLabel;
                    this.cancelButton.classList.remove("d-none");
                    this.hideButton?.classList.add("d-none");
                    break;
                case false:
                default:
                    editor.classList.remove("editing");
                    this.editButton.classList.remove("btn-success");
                    this.editButton.classList.add('btn-primary');
                    this.editButton.textContent = this.editLabel;
                    this.cancelButton.classList.add("d-none");
                    this.hideButton?.classList.remove("d-none");

                    // If editor is empty, then add the empty message
                    if(!this.state.html) {
                        editorEmptyTranscriptionMsg.classList.remove('d-none');
                    }
            }
        }
    }

    edit() {
        switch (!this.state.editable) {
            case false:
                flushSync(() => {
                    this.setState({
                        editable: false
                    })
                });

                this.save();
                break;
            case true:
            default:
                flushSync(() => {
                    this.setState({
                        editable: true
                    })
                });
        }

        this.updateUi(this.editor.isReadOnly);
    }

    save() {
        axios.put('/cards/' + this.cardId + '/editor', {
            html: this.state.html,
            box: this.props.reference
        }).then(response => {
            console.log(response);
            this.setState({
                // We copy the saved html to the original state
                // We use cloneDeep to avoid a reference
                original: _.cloneDeep(this.state.html)
            });
        }).catch(error => {
            console.log(error)
            // Display an error message to the user
            document.getElementById(this.editorErrorMsgId)
                .classList
                .remove("d-none");
        });
    }

    cancel() {
        if(this.state.editable) {
            flushSync(() => {
                this.setState({
                        // We restore the html initially loaded from the db
                        // We use cloneDeep to avoid a reference
                        html: _.cloneDeep(this.state.original ?? ''),
                        // We disable the edition mode
                        editable: false
                    },
                    () => this.updateUi()
                )
            });
        }
    }

    onEditorChange() {
        this.setState( {
            html: this.editor.getData()
        });
    }

    render() {
        return (
            <div>
                <CKEditor
                    editor={ this.editor }
                    data={ this.state.html }
                    config={ editorConfiguration }
                    onReady={ editor => {
                        this.editor = editor
                        //console.log(Array.from( editor.ui.componentFactory.names() ));
                    } }
                    disabled={ !this.state.editable }
                    onChange={ this.onEditorChange }
                    onBlur={ ( event, editor ) => {
                        //console.log( 'Blur.', editor );
                    } }
                    onFocus={ ( event, editor ) => {
                        //console.log( 'Focus.', editor );
                    } }
                />
            </div>
        )
    }
}

const elementIdBox3 = 'rct-editor-box3';
if (document.getElementById(elementIdBox3)) {
    const root = createRoot(document.getElementById(elementIdBox3));

    let data = document.getElementById(elementIdBox3).getAttribute('data');
    let reference = document.getElementById(elementIdBox3).getAttribute('reference');
    root.render(<Editor data={ data } reference={ reference } />);
}

const elementIdBox4 = 'rct-editor-box4';
if (document.getElementById(elementIdBox4)) {
    const root = createRoot(document.getElementById(elementIdBox4));

    let data = document.getElementById(elementIdBox4).getAttribute('data');
    let reference = document.getElementById(elementIdBox4).getAttribute('reference');
    root.render(<Editor data={ data } reference={ reference } />);
}

const elementIdBox2 = 'rct-editor-box2';
if (document.getElementById(elementIdBox2)) {
    const root = createRoot(document.getElementById(elementIdBox2));

    let data = document.getElementById(elementIdBox2).getAttribute('data');
    let reference = document.getElementById(elementIdBox2).getAttribute('reference');
    root.render(<Editor data={ data } reference={ reference } />);
}
