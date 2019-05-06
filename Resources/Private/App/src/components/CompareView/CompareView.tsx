import React, { SFC } from "react";

import TabView from "../TabView";
import CodeView from "../CodeView";
import RenderView from "../RenderView";

import style from "./style.css";

interface CompareViewProps {
  className?: string;
  propSetName: string;
  prototypeName: string;
}

const CompareView: SFC<CompareViewProps> = ({
  className,
  propSetName,
  prototypeName
}) => {
  const buildTabs = () => {
    const CodeViewTab = {
      icon: "code",
      content: <CodeView />
    };

    const RenderViewTab = {
      icon: "far fa-images",
      content: <RenderView />
    };

    return [CodeViewTab, RenderViewTab];
  };

  return (
    <div className={className}>
      <h1 className={style.headline}>{prototypeName}</h1>
      <h2 className={style.headline}>PropSet: {propSetName}</h2>
      <TabView tabs={buildTabs()} />
    </div>
  );
};

export default CompareView;
