import React, { SFC } from 'react';

import SitePackageDropdown from '../SitePackageDropdown';

interface HeaderProps {
    className?: string
}

const Header: SFC<HeaderProps> = ({className}) => {
    return <div className={className}>
        <SitePackageDropdown />
    </div>
}

export default Header;