import React, { Component } from 'react';
import { createRoot } from "react-dom/client";

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
        this.isLocal = data.isLocal ?? true;
        this.locale = data.locale ?? 'fr';
        this.type = this.isLocal ? this.card.file.type : this.guessMediaType(data.url);
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
            start: this.isLocal ? this.card.options.box1.start : null,
            end: this.isLocal ? this.card.options.box1.end : null
        }
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

    onPlayerReady(player){
        //console.log("Player is ready: ", player);
        this.player = player;
    }

    onVideoPlay(duration){
        //console.log("Video played at: ", duration);
    }

    onVideoPause(duration){
        // On pause, we get back a little bit to facilitate the transcription process
        const OFFSET = 1.0;
        if ( this.player.currentTime() > OFFSET ) {
            this.player.currentTime(this.player.currentTime() - OFFSET);
        } else {
            this.player.currentTime(0);
        }
    }

    onVideoTimeUpdate(duration){
        if(this.isLocal && this.card.options.box2.sync) {
            // keep the transcription in sync with the media player
            let view = document.getElementById("transcription-viewer");
            view.scrollTop =
                (view.scrollHeight * this.player.currentTime() / this.player.duration()) - (view.clientHeight / 2);
        }
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

    userActions(event) {
        if (event.shiftKey) {
            // Shift + Right arrow (play/pause)
            if (event.which === 39) {
                this.player.paused() ? this.player.play() : this.player.pause();
            }

            // Shift + Left arrow (seek back 2s)
            if (event.which === 37) {
                const OFFSET = 2.0;
                if ( this.player.currentTime() > OFFSET ) {
                    this.player.currentTime(this.player.currentTime() - OFFSET);
                } else {
                    this.player.currentTime(0);
                }
            }

            // Shift + Down arrow (speed slow/default speed)
            if (event.which === 40) {
                this.player.playbackRate(this.player.playbackRate() === 1 ? 0.5 : 1);
            }
        }

        // Stop propagation of the event
        event.preventDefault();
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
                        userActions={this.userActions.bind(this)}
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
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<Player data={ data } />);
}
