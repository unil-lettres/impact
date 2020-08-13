import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import Plyr from 'plyr';
import 'plyr/dist/plyr.css'

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
        this.config = playerConfiguration;
        this.source = {
            type: data.file.type,
            sources: [
                {
                    src: data.url,
                    type: this.sourceType(data.file.type),
                },
            ],
        };
    }

    componentDidMount() {
        this.player = new Plyr('.js-plyr', this.config);
        this.player.source = this.source;
    }

    componentWillUnmount() {
        this.player.destroy()
    }

    sourceType(type) {
        switch (type) {
            case 'video':
                return 'video/mp4';
            case 'audio':
            default:
                return 'audio/mpeg';
        }
    }

    render() {
        return (
            <video className="js-plyr plyr">
            </video>
        )
    }
}

const elementId = 'rct-player';
if (document.getElementById(elementId)) {
    let data = document.getElementById(elementId).getAttribute('data');
    ReactDOM.render(<Player data={ data } />, document.getElementById(elementId));
}
