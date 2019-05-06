import React, { SFC } from 'react';
import { connect } from 'react-redux';

import LoadingScreen from '../LoadingScreen';
import SnapshotUi from './SnapshotUi';

interface SnapshotUiContainerProps {
    loading: boolean
}

const SnapshoutUiContainer: SFC<SnapshotUiContainerProps> = ({loading}) => {
    if (loading) {
        return <LoadingScreen />
    }

    return <SnapshotUi />
}

const mapStateToProps = state => ({loading: state.loading})

export default connect(mapStateToProps)(SnapshoutUiContainer);