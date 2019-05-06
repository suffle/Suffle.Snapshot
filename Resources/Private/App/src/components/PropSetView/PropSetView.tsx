import React, { SFC } from "react";
import classnames from 'classnames'

import PropSetHeader from '../PropSetHeader';
import PropSetList from '../PropSetList';

import style from './style.css';


interface PropSetViewProps {
    className?: string
}

const PropSetView: SFC<PropSetViewProps> = ({
className
}) => {
  return <div className={classnames(style.prototypeSelectView, className)}>
    <PropSetHeader />
    <PropSetList />
  </div>
};

export default PropSetView;
