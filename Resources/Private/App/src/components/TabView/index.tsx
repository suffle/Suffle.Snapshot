import React, { SFC, useState } from "react";

import TabLabel from './TabLabel'

import styles from './style.css';

interface TabViewContainerProps {
  tabs: TabElement[];
  className?: string;
}

type TabElement = {
  label?: string;
  icon?: string;
  content: any;
};

const TabViewContainer: SFC<TabViewContainerProps> = ({ className, tabs }) => {
  const [selectedIndex, changeTab] = useState(0);
  const Labels = tabs.map(({ label, icon }, index) => (
    <TabLabel
      onClick={changeTab}
      label={label}
      index={index}
      icon={icon}
      key={'label-' + index}
      selected={index === selectedIndex}
    />
  ));
  const selectedTab = tabs[selectedIndex];

  return <div className={className}>
    <div className={styles.tabViewLabelContainer}>
        {Labels}
    </div>
    {selectedTab && selectedTab.content}
  </div>;
};

export default TabViewContainer;