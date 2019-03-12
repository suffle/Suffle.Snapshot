import React, { SFC } from 'react';

import Dropdown  from '../Dropdown';

interface SitePackageDropdownProps {
    options: string[],
    selected: string,
    onSiteChange(value): void
}

const SitePackageDropdown: SFC<SitePackageDropdownProps> = ({options, selected, onSiteChange}) => {
    return <Dropdown options={options} selectedValue={selected} onChange={onSiteChange} />;
}

export default SitePackageDropdown;