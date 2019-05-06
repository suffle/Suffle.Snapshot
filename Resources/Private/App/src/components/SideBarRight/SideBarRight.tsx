import React, { SFC } from 'react';
import SideBar from '@neos-project/react-ui-components/src/SideBar/';

import PropSetView from '../PropSetView';

interface SideBarLeftProps {
 className?: string
}

const SieBarLeft: SFC<SideBarLeftProps> = ({className}) => {
    return <SideBar className={className} position='right'>
        <PropSetView />
    </SideBar>
}

export default SieBarLeft;