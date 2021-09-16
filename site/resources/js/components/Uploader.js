import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Uppy from '@uppy/core'
import French from '@uppy/locales/lib/fr_FR'
import English from '@uppy/locales/lib/en_US'
import XHRUpload from '@uppy/xhr-upload';
import { DashboardModal } from '@uppy/react'
import DashboardComponent from "@uppy/react/src/Dashboard";

export default class Uploader extends Component {
    constructor (props) {
        super(props)

        let data = JSON.parse(this.props.data);

        this.state = {
            modalOpen: false
        }

        this.handleOpen = this.handleOpen.bind(this)
        this.handleClose = this.handleClose.bind(this)

        this.initVariables(data);
        this.initLocale();
        this.initUppy();
    }

    initVariables(data) {
        this.locale = data.locale ?? 'fr';
        this.label = data.label ?? 'Send file(s)';
        this.maxFileSize = data.maxFileSize ?? 500000000;
        this.maxNumberOfFiles = data.maxNumberOfFiles ?? 1;
        this.allowedFileTypes = data.allowedFileTypes ?? ['audio/*', 'video/*'];
        this.modal = data.modal ?? false;
    }

    initLocale () {
        switch(this.locale) {
            case 'fr':
                this.locale = French
                break;
            case 'en':
                this.locale = English
                break;
            default:
                this.locale = French
        }
    }

    initUppy () {
        this.uppy = new Uppy({
            debug: false,
            locale: this.locale,
            autoProceed: false,
            restrictions: {
                maxFileSize: this.maxFileSize,
                minNumberOfFiles: 1,
                maxNumberOfFiles: this.maxNumberOfFiles,
                allowedFileTypes: this.allowedFileTypes
            }
        }).use(XHRUpload, {
            limit: 1,
            endpoint: '/files/upload',
            formData: true,
            fieldName: 'file',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content')
            }
        });

        this.uppy.on('upload', (data) => {
            let course = document.getElementById('course_id') ?
                document.getElementById('course_id').value : null;
            let card = document.getElementById('card_id') ?
                document.getElementById('card_id').value : null;

            this.uppy.setOptions({
                meta: {
                    course: course,
                    card: card
                }
            });
        });

        this.uppy.on('complete', (result) => {
            if(result.successful[0] !== undefined) {
                console.log(result.successful[0].response.body);
            }
        });
    }

    handleOpen () {
        this.setState({
            modalOpen: true
        })
    }

    handleClose () {
        this.setState({
            modalOpen: false
        })
    }

    render () {
        if(this.modal) {
            return (
                <div>
                    <button className="btn btn-primary"
                            onClick={this.handleOpen}>
                        {this.label}
                    </button>
                    <DashboardModal
                        uppy={this.uppy}
                        closeModalOnClickOutside
                        open={this.state.modalOpen}
                        onRequestClose={this.handleClose}
                        proudlyDisplayPoweredByUppy={false}
                    />
                </div>
            );
        } else {
            return (
                <div>
                    <DashboardComponent
                        uppy={this.uppy}
                        proudlyDisplayPoweredByUppy={false}
                        height={360}
                    />
                </div>
            );
        }
    }
}

const elementId = 'rct-uploader';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    ReactDOM.render(<Uploader data={ data } />, document.getElementById(elementId));
}
