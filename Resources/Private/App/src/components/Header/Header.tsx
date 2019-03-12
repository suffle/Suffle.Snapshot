import React, { SFC } from 'react';

import SitePackageDropdown from '../SitePackageDropdown';

interface HeaderProps {}

const Header: SFC<HeaderProps> = (props) => {
    return <div>
        <SitePackageDropdown />
    </div>
}
export default Header;