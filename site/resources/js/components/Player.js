import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import VideoPlayer from './Video';
import French from 'video.js/dist/lang/fr.json';

import MimeType from 'mime-types/index';

export default class Player extends Component {
    constructor (props) {
        super(props)

        let data = JSON.parse(this.props.data);

        this.initVariables(data);
    }

    initVariables(data) {
        this.card = data.card;
        this.file = this.card.file ?? null;
        this.locale = data.locale ?? 'fr';
        this.type = this.file ? this.card.file.type : this.guessMediaType(data.url);
        this.mime = this.mediaMimeType(data.url);

        this.options = {
            autoplay: false,
            controls: true,
            fluid: true,
            preload: 'auto',
            language: this.locale,
            languages: {
                fr: French
            },
            sources: [{
                src: data.url,
                type: this.mime
            }]
        }

        this.offset = {
            start: this.card.options.box1.start,
            end: this.card.options.box1.end
        }
    }

    onPlayerReady(player){
        //console.log("Player is ready: ", player);
        this.player = player;
    }

    onVideoPlay(duration){
        //console.log("Video played at: ", duration);
    }

    onVideoPause(duration){
        //console.log("Video paused at: ", duration);
    }

    onVideoTimeUpdate(duration){
        //console.log("Time updated: ", duration);
    }

    onVideoSeeking(duration){
        //console.log("Video seeking: ", duration);
    }

    onVideoSeeked(from, to){
        //console.log(`Video seeked from ${from} to ${to}`);
    }

    onVideoEnd(){
        //console.log("Video ended");
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
                    ? <VideoPlayer
                        onReady={this.onPlayerReady.bind(this)}
                        onPlay={this.onVideoPlay.bind(this)}
                        onPause={this.onVideoPause.bind(this)}
                        onTimeUpdate={this.onVideoTimeUpdate.bind(this)}
                        onSeeking={this.onVideoSeeking.bind(this)}
                        onSeeked={this.onVideoSeeked.bind(this)}
                        onEnd={this.onVideoEnd.bind(this)}
                        offset={this.offset}
                        { ...this.options }
                    />
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
