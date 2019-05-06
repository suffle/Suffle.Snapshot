import React, { SFC } from 'react';

import Header from '../Header';
import MainContent from '../MainContent';

import style from './style.css'

interface SnapshotUiProps {}

const SnapshotUi: SFC<SnapshotUiProps> = () => {
    return <div className={style.snapshotUi}>
        <Header className={style.header} />
        <MainContent className={style.mainContent} />
    </div>
}

export default SnapshotUi;