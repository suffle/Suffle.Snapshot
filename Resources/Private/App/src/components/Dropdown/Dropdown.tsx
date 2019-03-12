import React, { SFC, useState } from "react";

import style from "./style.css";

interface DropdownProps {
  options: string[];
  selectedValue: string;
  onChange(value): void;
  renderItem?(item): any;
}

interface ListProps {
  options: string[];
  isOpen: boolean;
  onChange(value): void;
  ItemRenderer?(item): any;
}

interface ItemProps {
  item: string;
  onClick(itemName): void;
}

const DefaultItemRenderer: SFC<ItemProps> = ({ item, onClick }) => {
  const onClickEvent = () => {
    onClick(item);
  };
  return (
    <button className={style.site} onClick={onClickEvent}>
      <div className={style.title}>{item}</div>
    </button>
  );
};

const DropdownList: SFC<ListProps> = ({
  options,
  ItemRenderer,
  onChange,
  isOpen
}) => {
  return isOpen ? (
    <div className={style.list}>
      <div className={style.sites}>
        {options.map(option => (
          <ItemRenderer
            key={"dropdownOption-" + option}
            item={option}
            onClick={onChange}
          />
        ))}
      </div>
    </div>
  ) : null;
};

const Dropdown: SFC<DropdownProps> = ({
  options,
  selectedValue,
  onChange,
  renderItem
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const ItemRenderer = renderItem || DefaultItemRenderer;

  const onClose = () => {
    setIsOpen(false);
  };

  const toggle = () => {
    setIsOpen(!isOpen);
  };

  const onValueChange = value => {
    onClose();
    onChange(value);
  };

  const filteredOptionsList = options.filter(
    option => option !== selectedValue
  );
  const hasOptions = filteredOptionsList && filteredOptionsList.length;

  return (
    <div className={style.container}>
      <button
        onClick={toggle}
        className={style.selector}
        disabled={!hasOptions}
      >
        {selectedValue}
      </button>
      {hasOptions ? (
        <>
          {isOpen ? <div className={style.overlay} onClick={onClose} /> : null}
          <DropdownList
            isOpen={isOpen}
            ItemRenderer={ItemRenderer}
            onChange={onValueChange}
            options={filteredOptionsList}
          />
        </>
      ) : null}
    </div>
  );
};

export default Dropdown;
