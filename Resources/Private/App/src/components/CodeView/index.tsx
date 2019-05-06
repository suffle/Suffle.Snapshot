import React, { SFC } from 'react';
import {connect} from 'react-redux';

import CodeView from './CodeView';
import { Prototype } from '../../type/Prototype';

interface CodeViewContainerProps {
    className?: string,
    propSet: Prototype.PropSet,
    prototypeName: string,
    propSetName: string
}

const CodeViewContainer: SFC<CodeViewContainerProps> = (props) => {
    return <CodeView {...props} />
}

const mapStateToProps = ({currentPrototype}) => {
    const propSets = currentPrototype && currentPrototype.data;
    const selectedPropSet = currentPrototype && currentPrototype.currentPropSet;

    return {
        prototypeName: currentPrototype && currentPrototype.name,
        propSetName: selectedPropSet,
        propSet: propSets && propSets.hasOwnProperty(selectedPropSet) && propSets[selectedPropSet]
    }
}

export default connect(mapStateToProps)(CodeViewContainer);