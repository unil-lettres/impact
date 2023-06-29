import React, {Component} from 'react';

import videojs from 'video.js'
import 'video.js/dist/video-js.css';
import offset from  'videojs-offset/dist/videojs-offset';

export default class VideoPlayer extends Component {
    componentDidMount() {
        // Instantiate Video.js
        this.player = videojs(
            this.videoNode,
            this.props
        );

        // Add offset if available
        videojs.registerPlugin("offset", offset);
        if(this.props.offset.start || this.props.offset.end) {
            this.player.offset({
                start: this.props.offset.start,
                end: this.props.offset.end,
                restart_beginning: false
            });
        }

        // Init player events
        this.initEvents(this.props);
    }

    // Destroy player
    componentWillUnmount() {
        if (this.player) {
            this.player.dispose()
        }
    }

    initEvents(props) {
        let currentTime = 0;
        let previousTime = 0;
        let position = 0;

        this.player.ready(() => {
            props.onReady(this.player);
            window.player = this.player;
        });

        this.player.on('play', () => {
            props.onPlay(this.player.currentTime());
        });

        this.player.on('pause', () => {
            props.onPause(this.player.currentTime());
        });

        this.player.on('timeupdate', (e) => {
            props.onTimeUpdate(this.player.currentTime());
            previousTime = currentTime;
            currentTime = this.player.currentTime();
            if (previousTime < currentTime) {
                position = previousTime;
                previousTime = currentTime;
            }
        });

        this.player.on('seeking', () => {
            this.player.off('timeupdate', () => { });
            this.player.one('seeked', () => { });
            props.onSeeking(this.player.currentTime());
        });

        this.player.on('seeked', () => {
            let completeTime = Math.floor(this.player.currentTime());
            props.onSeeked(position, completeTime);
        });

        this.player.on('ended', () => {
            props.onEnd();
        });

        this.player.on('keydown', (e) => {
            props.userActions(e);
        });
    }

    render() {
        return (
            <div>
                <div data-vjs-player>
                    <video
                        ref={ node => this.videoNode = node }
                        className="video-js vjs-big-play-centered">
                    </video>
                </div>
            </div>
        )
    }
}

VideoPlayer.defaultProps = {
    onReady: () => { },
    onPlay: () => { },
    onPause: () => { },
    onTimeUpdate: () => { },
    onSeeking: () => { },
    onSeeked: () => { },
    onEnd: () => { },
    userActions: () => { },
    offset: { start: null, end: null },
    autoplay: false,
    controls: true,
    fluid: true,
    preload: 'auto',
    language: 'fr',
    languages: {},
    sources: [{ src: "", type: "" }]
}
