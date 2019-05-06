import React, { SFC } from 'react';
import classnames from 'classnames';

import Icon from "@neos-project/react-ui-components/src/Icon";

import styles from './tabLabel.css';

interface TabLabelProperties {
    index: number,
    label?: string,
    icon?: string;
    selected: boolean,
    onClick(index): void;
}

const TabLabel: SFC<TabLabelProperties> = ({index, icon, label, selected, onClick}) => {
    const onLabelClick = () => onClick(index);

    return <div onClick={onLabelClick} className={classnames(styles.labelContainer, {[styles['labelContainer--highlighted']]: selected})}>
       {icon && <Icon icon={icon} className={styles.labelIcon} />}
       {label && <span>{label}</span>}
    </div>
}

export default TabLabel;