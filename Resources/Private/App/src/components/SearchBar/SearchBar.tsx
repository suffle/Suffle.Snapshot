import React, { SFC } from "react";
import classnames from 'classnames';

import TextInput from "@neos-project/react-ui-components/src/TextInput";
import Icon from "@neos-project/react-ui-components/src/Icon";
import IconButton from "@neos-project/react-ui-components/src/IconButton";

import style from './style.css';

interface SearchBarProps {
  value: string;
  placeholder?: string;
  className?: string;
  onChange(value): void;
  onFocus?(): void;
  onBlur?(): void;
  onClearClick?(): void;
  focused?: boolean;
}

const SearchBar: SFC<SearchBarProps> = ({
  className,
  value,
  placeholder,
  onChange,
  onFocus,
  onBlur,
  onClearClick,
  focused
}) => {
  const showClear = value.length > 0 && onClearClick;

  return (
    <div className={classnames(style.wrapper, className)}>
      {focused ? null : <Icon icon="search" className={style.placeholderIcon} />}
      <TextInput
        placeholder={placeholder}
        onChange={onChange}
        onFocus={onFocus}
        onBlur={onBlur}
        type="search"
        value={value}
        containerClassName={classnames(style.searchInput,{[style['searchInput--focused']]: focused})}
      />
      {showClear && <IconButton icon="times" onClick={onClearClick} className={classnames(style.clearButton, {[style['clearButton--focused']]: focused})} />}
    </div>
  );
};

export default SearchBar;
