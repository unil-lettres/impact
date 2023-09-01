import React from 'react';
import { createRoot } from "react-dom/client";

import Uploader from "./Uploader";

export default class Attachments extends Uploader {
    constructor(props){
        super(props);
    }

    get allowedFileTypes() {
        // All types are allowed for attachments
        return null;
    }

    get attachment() {
        return true;
    }
}

const elementId = 'rct-attachments';
if (document.getElementById(elementId)) {
    const root = createRoot(document.getElementById(elementId));

    let data = document.getElementById(elementId).getAttribute('data');
    root.render(<Attachments data={ data } />);
}
