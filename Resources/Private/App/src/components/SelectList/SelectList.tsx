import React, { SFC } from "react";
import isEqual from "lodash.isequal";
import classnames from "classnames";

import DefautlItem from "./DefaultItem";

import style from "./style.css";

type ListItemProps = {
  label?: string;
  value: string;
  onClick(value): void;
} & {
  [prop: string]: any;
};

interface SelectListProps {
  className?: string;
  items: any[];
  selectedItem: any;
  itemRenderer?: React.ComponentType<ListItemProps>;
  onSelect(value): void;
}

const SelectList: SFC<SelectListProps> = ({
  className,
  items,
  selectedItem,
  itemRenderer,
  onSelect
}) => {
  const Item = itemRenderer || DefautlItem;

  return (
    <div className={classnames(style.selectListContainer, className)}>
    <ul className={classnames(style.selectList, className)}>
      {items.map((item, index) => {
        const { value, label, ...rest } = item;
        const itemValue = value || item;
        return (
          <Item
            value={value || item}
            label={item.label}
            onClick={onSelect}
            selected={isEqual(itemValue, selectedItem)}
            key={index}
            {...rest}
          />
        );
      })}
    </ul>
    </div>
  );
};

export default SelectList;
