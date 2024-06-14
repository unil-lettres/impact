import React, { Component } from 'react';
import { createRoot } from "react-dom/client";
import { flushSync } from 'react-dom';

import axios from "axios";
import _ from "lodash";

import { CKEditor } from '@ckeditor/ckeditor5-react';
import InlineEditor from '@ckeditor/ckeditor5-editor-inline/src/inlineeditor';

import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import StrikethroughPlugin from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import HighlightPlugin from '@ckeditor/ckeditor5-highlight/src/highlight';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import ListProperties from '@ckeditor/ckeditor5-list/src/listproperties';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import AlignmentPlugin from '@ckeditor/ckeditor5-alignment/src/alignment';
import MediaEmbedPlugin from '@ckeditor/ckeditor5-media-embed/src/mediaembed';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageInsertPlugin from '@ckeditor/ckeditor5-image/src/imageInsert';
import ImageResizePlugin from '@ckeditor/ckeditor5-image/src/imageresize';
import Base64UploadAdapter from '@ckeditor/ckeditor5-upload/src/adapters/base64uploadadapter';
import FontBackgroundColor from '@ckeditor/ckeditor5-font/src/fontbackgroundcolor';
import FontColorPlugin from '@ckeditor/ckeditor5-font/src/fontcolor';
import FontSizePlugin from '@ckeditor/ckeditor5-font/src/fontsize';
import TablePlugin from '@ckeditor/ckeditor5-table/src/table';
import TableToolbarPlugin from '@ckeditor/ckeditor5-table/src/tabletoolbar';
import TableCellProperties from '@ckeditor/ckeditor5-table/src/tablecellproperties';
import TableProperties from '@ckeditor/ckeditor5-table/src/tableproperties';
import HorizontalLinePlugin from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline';
import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import HeadingPlugin from '@ckeditor/ckeditor5-heading/src/heading';

const editorConfiguration = {
    plugins: [
        EssentialsPlugin,
        BoldPlugin,
        ItalicPlugin,
        UnderlinePlugin,
        HeadingPlugin,
        StrikethroughPlugin,
        BlockQuotePlugin,
        ListProperties,
        LinkPlugin,
        AlignmentPlugin,
        MediaEmbedPlugin,
        ImagePlugin,
        ImageCaptionPlugin,
        ImageStylePlugin,
        ImageToolbarPlugin,
        ImageInsertPlugin,
        ImageResizePlugin,
        Base64UploadAdapter,
        FontColorPlugin,
        FontBackgroundColor,
        FontSizePlugin,
        TablePlugin,
        TableToolbarPlugin,
        TableCellProperties,
        TableProperties,
        HighlightPlugin,
        HorizontalLinePlugin,
        ParagraphPlugin
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
        "link",
        "mediaEmbed",
        "uploadImage"
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
    },
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

    updateEditorConfiguration(data) {
        editorConfiguration.language = data.locale ?? 'fr';
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
