import React, { SFC } from 'react';
import { connect } from 'react-redux';

import { Prototype } from '../../type/Prototype';
import { setCurrentPropSet } from '../../actions/prototype';

import PropSetList from './PropSetList';

interface PropSetListContainerProps {
    className?: string,
    propSets: Prototype.PrototypeData;
    selectedPropSet: string,
    onPropSetSelect(propSetName: string): void
}

const PropSetListContainer: SFC<PropSetListContainerProps> = ({className, propSets, selectedPropSet, onPropSetSelect}) => {
    const propSetNames = propSets ? Object.keys(propSets).map(key => {
        return {
            value: key,
            testSuccess: propSets[key].testSuccess
        }
    }) : [];

    return <PropSetList className={className} propSets={propSetNames} selectedPropSet={selectedPropSet} onPropSetSelect={onPropSetSelect} />

}

const mapStateToProps = ({currentPrototype}) => ({
    propSets: currentPrototype && currentPrototype.data,
    selectedPropSet: currentPrototype && currentPrototype.currentPropSet
})

const mapDispatchToProps = {
    onPropSetSelect: setCurrentPropSet
}

export default connect(mapStateToProps, mapDispatchToProps)(PropSetListContainer);