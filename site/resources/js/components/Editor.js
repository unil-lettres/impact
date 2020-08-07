import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import CKEditor from '@ckeditor/ckeditor5-react';
import BalloonEditor from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';

import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import StrikethroughPlugin from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import HighlightPlugin from '@ckeditor/ckeditor5-highlight/src/highlight';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';
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
        ListPlugin,
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

        let data = JSON.parse(this.props.data);

        this.edit = this.edit.bind(this)

        this.updateEditorConfiguration(data);
        this.initVariables(data);
        this.updateButton(this.disabled);
    }

    componentDidMount() {
        document.getElementById(this.editButtonId)
            .addEventListener('click', this.edit, false);
    }

    updateEditorConfiguration(data) {
        editorConfiguration.language = data.locale ?? 'fr';
    }

    initVariables(data) {
        this.editor = BalloonEditor;
        this.config = editorConfiguration;
        this.html = data.html ?? '';
        this.disabled = data.disabled ?? true;
        this.editButtonId = 'edit-' + this.props.reference;
        this.editorId = 'rct-editor-' + this.props.reference;
        this.editLabel = data.editLabel ?? 'Edit';
        this.saveLabel = data.saveLabel ?? 'Save';
    }

    updateButton(isReadOnly) {
        let button = document.getElementById(this.editButtonId);
        let editor = document.getElementById(this.editorId);

        switch (isReadOnly) {
            case true:
                editor.classList.remove("editing");
                button.classList.remove("btn-success");
                button.classList.add('btn-primary');
                button.textContent = this.editLabel;
                break;
            case false:
            default:
                editor.classList.add('editing');
                button.classList.remove("btn-primary");
                button.classList.add('btn-success');
                button.innerText = this.saveLabel;
        }
    }

    edit(event){
        switch (this.editor.isReadOnly) {
            case false:
                this.editor.isReadOnly = true;

                // TODO: save text to database here with axios
                break;
            case true:
            default:
                this.editor.isReadOnly = false;
        }

        this.updateButton(this.editor.isReadOnly);
    }

    render() {
        return (
            <div>
                <CKEditor
                    editor={ this.editor }
                    data={ this.html }
                    config={ this.config }
                    onInit={ editor => {
                        this.editor = editor
                        //console.log(Array.from( editor.ui.componentFactory.names() ));
                        //console.log( 'Editor is ready to use!', this.editor );
                    } }
                    disabled={ this.disabled }
                    onChange={ ( event, editor ) => {
                        const data = editor.getData();
                        console.log( { event, editor, data } );
                    } }
                    onBlur={ ( event, editor ) => {
                        console.log( 'Blur.', editor );
                    } }
                    onFocus={ ( event, editor ) => {
                        console.log( 'Focus.', editor );
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
