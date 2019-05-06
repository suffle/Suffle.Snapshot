import React, {SFC } from 'react';
import {connect} from 'react-redux';

import CompareView from './CompareView';
import { Prototype } from '../../type/Prototype';

interface CompareViewContainerProps {
    className?: string,
    propSet: Prototype.PropSet,
    prototypeName: string,
    propSetName: string
}

const CompareViewContainer: SFC<CompareViewContainerProps> = (props) => {
    return <CompareView {...props} />
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

export default connect(mapStateToProps)(CompareViewContainer);