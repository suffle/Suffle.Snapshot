import React, { SFC } from 'react';

import { ReactGhLikeDiff } from "react-gh-like-diff";
import prettifyXml from "../../utils/prettifyXml";
import { Prototype } from '../../type/Prototype';

import style from './style';

interface CodeViewProps {
    propSet: Prototype.PropSet;
    propSetName: string;
    prototypeName: string;
}

const CodeView: SFC<CodeViewProps> = ({propSet, propSetName, prototypeName}) => {
    const prettyOld =
    propSet && propSet.snapshot ? prettifyXml(propSet.snapshot) : '';
  const prettyNew =
    propSet && propSet.current ? prettifyXml(propSet.current) : '';

    return <div className={style.diffView}>
    <ReactGhLikeDiff past={prettyOld} current={prettyNew} options={{
      originalFileName: prototypeName,
      updatedFileName: propSetName
    }} />}
  </div>
}

export default CodeView;