import React, { SFC, useState } from "react";
import debounce from "lodash.debounce";
import SearchBar from "./SearchBar";

interface SearchBarContainerProps {
  placeholder?: string;
  className?: string;
  onChange(value): void;
  onClearClick?(): void;
}

const SearchBarContainer: SFC<SearchBarContainerProps> = ({
  className,
  placeholder,
  onChange,
  onClearClick
}) => {
  const [searchFocused, setSearchFocused] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");

  const onFocus = () => {
    setSearchFocused(true);
  };

  const onBlur = () => {
    setSearchFocused(false);
  };

  const debouncedChange = debounce(onChange, 250);

  const onValueChange = value => {
    setSearchTerm(value);
    debouncedChange(value);
  };

  const onClearButtonClick = () => {
    setSearchTerm("");
    onClearClick();
  };

  return (
    <SearchBar
      className={className}
      value={searchTerm}
      placeholder={placeholder}
      onChange={onValueChange}
      onFocus={onFocus}
      onBlur={onBlur}
      onClearClick={onClearButtonClick}
      focused={searchFocused}
    />
  );
};

SearchBarContainer.defaultProps = {
  placeholder: "Search"
};

export default SearchBarContainer;
