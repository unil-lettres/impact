import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import Plyr from 'plyr';
import 'plyr/dist/plyr.css'
import MimeType from 'mime-types/index';

const playerConfiguration = {
    controls: [
        'play',
        'progress',
        'mute',
        'volume',
        'pip',
        'fullscreen',
    ]
};

export default class Player extends Component {
    constructor (props) {
        super(props)

        let data = JSON.parse(this.props.data);

        this.initVariables(data);
    }

    initVariables(data) {
        this.card = data.card
        this.type = this.card.file ? this.card.file.type : this.guessMediaType(data.url);
        this.mime = this.mediaMimeType(data.url);
        this.config = playerConfiguration;
        this.source = {
            type: this.type,
            sources: [
                {
                    src: data.url,
                    type: this.mime,
                },
            ],
        };
    }

    componentDidMount() {
        if(this.isMediaReachable()) {
            this.player = new Plyr('.js-plyr', this.config);
            this.player.source = this.source;
            this.player.speed = 1;
        }
    }

    componentWillUnmount() {
        this.player.destroy()
    }

    mediaMimeType(path) {
        return this.getMimeType(path) ?? null;
    }

    guessMediaType(path) {
        let mimeType = this.getMimeType(path);

        if(!mimeType) {
            return null;
        }

        if(mimeType.startsWith("video")) {
            return 'video';
        }

        if(mimeType.startsWith("audio")) {
            return 'audio';
        }

        return null;
    }

    getMimeType(path) {
        return MimeType.lookup(path);
    }

    isMediaReachable() {
        return !!this.mime;
    }


    render() {
        const isMediaReachable = this.isMediaReachable();
        return (
            <div>
                { isMediaReachable
                    ? <video className="js-plyr plyr"></video>
                    : <p className="text-danger text-center p-3">Cannot load the media</p>
                }
            </div>
        )
    }
}

const elementId = 'rct-player';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    ReactDOM.render(<Player data={ data } />, document.getElementById(elementId));
}
