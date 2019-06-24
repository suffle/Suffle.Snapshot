import React, { SFC, useState } from "react";
import { connect } from "react-redux";

import SearchBar from '../SearchBar';
import SelectList from '../SelectList';
import { setCurrentPrototype } from '../../actions/prototype';

import style from './style.css';


interface PrototypeSelectViewContainerProps {
  prototypes;
  selectedPrototype;
  setPrototype(prototypeName): void;
}

const PrototypeSelectViewContainer: SFC<PrototypeSelectViewContainerProps> = ({
  prototypes,
  selectedPrototype,
  setPrototype
}) => {
  const [searchTerm, setSearchTerm] = useState("");

  const onSearchChange = (value) => {
    setSearchTerm(value);
  }

  const onClearClick = () => {
    setSearchTerm('');
  }

  const filteredPrototypes = searchTerm ? prototypes.filter(prototype => prototype.includes(searchTerm)) : prototypes

  return <div className={style.prototypeSelectView}>
      <SearchBar className={style.prototypeSelectViewSearch} onChange={onSearchChange} onClearClick={onClearClick} />
      <SelectList className={style.prototypeSelectViewList} items={filteredPrototypes} selectedItem={selectedPrototype} onSelect={setPrototype} />
  </div>
};

const mapStateToProps = ({ availablePrototypes, currentPrototype }) => ({
  prototypes: availablePrototypes,
  selectedPrototype: currentPrototype && currentPrototype.name
});

const mapDispatchToProps = {
    setPrototype: setCurrentPrototype
}

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(PrototypeSelectViewContainer);
