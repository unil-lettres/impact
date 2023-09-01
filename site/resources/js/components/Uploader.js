import React, { Component } from 'react';

import Uppy from '@uppy/core'
import French from '@uppy/locales/lib/fr_FR'
import English from '@uppy/locales/lib/en_US'
import XHRUpload from '@uppy/xhr-upload';
import { DashboardModal, Dashboard } from '@uppy/react'

export default class Uploader extends Component {
    constructor (props) {
        super(props);

        let data = JSON.parse(this.props.data);

        this.state = {
            modalOpen: false,
            successfulUpload: false
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
        this.modal = data.modal ?? true;
        this.reloadOnModalClose = data.reloadOnModalClose ?? true;
        this.course_id = data.course_id ?? null;
        this.card_id = data.card_id ?? null;
        this.note = data.note ?? null;
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
            this.uppy.setOptions({
                meta: {
                    course_id: this.course_id,
                    card_id: this.card_id,
                    attachment: this.attachment
                }
            });
        });

        this.uppy.on('complete', (result) => {
            if (result.failed.length > 0) {
                console.error('Errors:');
                result.failed.forEach((file) => {
                    console.error(file.error);
                });
            }
        });

        this.uppy.on('upload-success', (file, response) => {
            this.setState({
                successfulUpload: true
            })
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

        if(this.reloadOnModalClose && this.state.successfulUpload) {
            // Reload page only if one or more files were uploaded
            // successfully & reload option is set to true.
            window.location.reload();
        }
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
                        note={this.note}
                        proudlyDisplayPoweredByUppy={false}
                    />
                </div>
            );
        } else {
            return (
                <div>
                    <Dashboard
                        uppy={this.uppy}
                        proudlyDisplayPoweredByUppy={false}
                        height={360}
                    />
                </div>
            );
        }
    }
}
