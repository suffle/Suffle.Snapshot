import React, { SFC } from 'react';
import classnames from 'classnames';

import SideBarLeft from '../SideBarLeft';
import SideBarRight from '../SideBarRight';
import CompareView from '../CompareView';

import style from './style.css';

interface MainContentProps {
    className?: string
}

const MainContent: SFC<MainContentProps> = ({className}) => {
    return <div className={classnames(className)}>
        <div className={style.mainContent}>
            <SideBarLeft className={style.sideBar} />
            <CompareView className={style.compareView} />
            <SideBarRight className={style.sideBar} />

        </div>
    </div>
}

export default MainContent;