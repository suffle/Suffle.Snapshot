import React, { SFC, useState } from 'react';
import { connect } from 'react-redux';

import MarkupRenderer from './MarkupRenderer'

interface MarkupRendererContainerProps {
    previewDocument: string;
    previewMarkup: string;
    className?: string;
}

const MarkupRendererContainer: SFC<MarkupRendererContainerProps> = (props) => {
    const [loading, setLoading] = useState(true);
    const generateMarkup = () => {
        const parser = new DOMParser();
        const parsedDocument = parser.parseFromString(props.previewDocument, 'text/html');
        const previewContainer = parsedDocument.querySelector('#preview-container')

        if (previewContainer) {
            previewContainer.innerHTML = props.previewMarkup;
            return parsedDocument.documentElement.outerHTML;
        }

        return '';
    }

    return <MarkupRenderer
        markup={generateMarkup()}
        loading={loading}
        setLoadingState={setLoading}
        className={props.className}
    />
}

const mapStateToProps = ({previewMarkup}) => {
    return {
        previewDocument: previewMarkup
    }
}

export default connect(mapStateToProps)(MarkupRendererContainer);
