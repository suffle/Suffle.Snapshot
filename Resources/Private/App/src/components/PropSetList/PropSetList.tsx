import React, { SFC } from "react";

import SelectList from "../SelectList";
import PropSetListItem from "./PropSetListItem";

type PropSetItem = {
  value: string;
  testSuccess: boolean;
};

interface PropSetListProps {
  className?: string;
  propSets: PropSetItem[];
  selectedPropSet?: string;
  onPropSetSelect(propSet: string): void;
}

const PropSetList: SFC<PropSetListProps> = ({
  className,
  propSets,
  selectedPropSet,
  onPropSetSelect
}) => {
  return (
    <div className={className}>
      <SelectList
        items={propSets}
        selectedItem={selectedPropSet}
        onSelect={onPropSetSelect}
        itemRenderer={PropSetListItem}
      />
    </div>
  );
};

export default PropSetList;
