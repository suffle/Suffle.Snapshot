import React, { SFC } from 'react';

import SelectBox  from '@neos-project/react-ui-components/src/SelectBox';

import style from './style.css'
interface SitePackageDropdownProps {
    options: string[],
    selected: string,
    onSiteChange(value): void
}

const SitePackageDropdown: SFC<SitePackageDropdownProps> = ({options, selected, onSiteChange}) => {
    const normalizedOptions = options.map(option => ({label: option, value: option}))

    return <div className={style.sitePackageDropdown}>
        <SelectBox  options={normalizedOptions} value={selected} onValueChange={onSiteChange} />
    </div>
}

export default SitePackageDropdown;