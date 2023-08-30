import React from 'react';
import { createRoot } from "react-dom/client";

import Uploader from "./Uploader";

export default class Files extends Uploader {
    constructor(props){
        super(props);
    }

    get allowedFileTypes() {
        return ['audio/*', 'video/*'];
    }

    get attachment() {
        return false;
    }
}

const elementId = 'rct-files';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<Files data={ data } />);
}
