import React, { SFC } from 'react';
import classnames from 'classnames';

import style from './propSetListItemStyle.css';
import itemStyle from '../SelectList/defaultItemStyle.css';

interface PropSetListItemProps {
    value: string,
    selected?: boolean,
    testSuccess: boolean,
    onClick(value: string): void
}

const PropSetListItem: SFC<PropSetListItemProps> = ({testSuccess, value, onClick, selected}) => {
    const onClickItem = () => {
        onClick(value);
    }
    const containerClasses = classnames(
        itemStyle.defaultListItem,
        style.propSetListItem,
        {
            [itemStyle.defaultListItemisSelected]: selected
        }
    )
    const indicatorStyles = classnames(
        style.successIndicator,
        {
            [style['successIndicator--fail']]: !testSuccess
        }
    )

    return <li onClick={selected ? null : onClickItem} className={containerClasses}>
        <span className={indicatorStyles} />
        <span>{value}</span>
    </li>
}

export default PropSetListItem;