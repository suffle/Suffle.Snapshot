import React, { SFC } from 'react';
import classnames from 'classnames';


import style from './defaultItemStyle.css';

interface DefaultItemProps {
    label?: string,
    value: string,
    selected?: boolean,
    onClick(value: string): void
}

const DefaultItem: SFC<DefaultItemProps> = ({label, value, onClick, selected}) => {
    const onClickItem = () => {
        onClick(value);
    }

    const itemLabel = label || value;
    const containerClasses = classnames(
        style.defaultListItem,
        {
            [style.defaultListItemisSelected]: selected
        }
    )

    return <li onClick={selected ? null : onClickItem} className={containerClasses}>
        <span>{itemLabel}</span>
    </li>
}

export default DefaultItem;