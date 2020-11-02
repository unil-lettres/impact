import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import axios from "axios";

import CKEditor from '@ckeditor/ckeditor5-react';
import BalloonEditor from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';

import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import StrikethroughPlugin from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import HighlightPlugin from '@ckeditor/ckeditor5-highlight/src/highlight';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import ListStyle from '@ckeditor/ckeditor5-list/src/liststyle';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import AlignmentPlugin from '@ckeditor/ckeditor5-alignment/src/alignment';
import MediaEmbedPlugin from '@ckeditor/ckeditor5-media-embed/src/mediaembed';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
import Base64UploadAdapter from '@ckeditor/ckeditor5-upload/src/adapters/base64uploadadapter';
import FontColorPlugin from '@ckeditor/ckeditor5-font/src/fontcolor';
import FontSizePlugin from '@ckeditor/ckeditor5-font/src/fontsize';
import TablePlugin from '@ckeditor/ckeditor5-table/src/table';
import TableToolbarPlugin from '@ckeditor/ckeditor5-table/src/tabletoolbar';
import HorizontalLinePlugin from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline';

const editorConfiguration = {
    plugins: [
        EssentialsPlugin,
        BoldPlugin,
        ItalicPlugin,
        UnderlinePlugin,
        StrikethroughPlugin,
        BlockQuotePlugin,
        ListStyle,
        LinkPlugin,
        AlignmentPlugin,
        MediaEmbedPlugin,
        ImagePlugin,
        ImageCaptionPlugin,
        ImageStylePlugin,
        ImageToolbarPlugin,
        ImageUploadPlugin,
        Base64UploadAdapter,
        FontColorPlugin,
        FontSizePlugin,
        TablePlugin,
        TableToolbarPlugin,
        HighlightPlugin,
        HorizontalLinePlugin
    ],
    toolbar: [
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
        "imageUpload"
    ],
    image: {
        upload: {
            types: [ 'png', 'jpeg' ],
            panel: {
                items: [ 'insertImageViaUrl' ]
            }
        },
        toolbar: [
            'imageStyle:full',
            'imageStyle:side',
            '|',
            'imageTextAlternative'
        ]
    }
};

export default class Editor extends Component {
    constructor (props) {
        super(props)

        const data = JSON.parse(this.props.data);
        const html = data.card[this.props.reference];
        const disabled = data.disabled ?? true;

        this.state = {
            original: _.cloneDeep(html),
            html: _.cloneDeep(html),
            editable: !disabled
        };

        this.edit = this.edit.bind(this)
        this.cancel = this.cancel.bind(this);
        this.onEditorChange = this.onEditorChange.bind(this);

        this.updateEditorConfiguration(data);
        this.initVariables(data);
        this.updateButton();
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
    }

    initVariables(data) {
        this.editor = BalloonEditor;
        this.config = editorConfiguration;
        this.card = data.card;
        this.editButtonId = 'edit-' + this.props.reference;
        this.editButton = document.getElementById(this.editButtonId);
        this.cancelButtonId = 'cancel-' + this.props.reference;
        this.cancelButton = document.getElementById(this.cancelButtonId);
        this.editorId = 'rct-editor-' + this.props.reference;
        this.editorErrorMsgId = 'edit-failed-' + this.props.reference;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
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

        this.updateButton(this.editor.isReadOnly);
    }

    save() {
        axios.put('/cards/' + this.card.id + '/editor', {
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
            this.setState({
                    // We restore the html initially loaded from the db
                    // We use cloneDeep to avoid a reference
                    html: _.cloneDeep(this.state.original),
                    // We disable the edition mode
                    editable: false
                },
                () => this.updateButton()
            )
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
                    config={ this.config }
                    onInit={ editor => {
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
    let data = document.getElementById(elementIdBox3).getAttribute('data');
    let reference = document.getElementById(elementIdBox3).getAttribute('reference');
    ReactDOM.render(<Editor data={ data } reference={ reference } />, document.getElementById(elementIdBox3));
}

const elementIdBox4 = 'rct-editor-box4';
if (document.getElementById(elementIdBox4)) {
    let data = document.getElementById(elementIdBox4).getAttribute('data');
    let reference = document.getElementById(elementIdBox4).getAttribute('reference');
    ReactDOM.render(<Editor data={ data } reference={ reference } />, document.getElementById(elementIdBox4));
}
