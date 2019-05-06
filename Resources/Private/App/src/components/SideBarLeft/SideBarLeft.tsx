import React, { SFC } from 'react';
import SideBar from '@neos-project/react-ui-components/src/SideBar/';

import PrototypeSelectView from '../PrototypeSelectView';

interface SideBarLeftProps {
 className?: string
}

const SieBarLeft: SFC<SideBarLeftProps> = ({className}) => {
    return <SideBar className={className} position='left' aria-hidden={'false'}>
        <PrototypeSelectView />
    </SideBar>
}

export default SieBarLeft;