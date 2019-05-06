import React, { SFC } from "react";
import { connect } from "react-redux";

import PropSetHeader from "./PropSetHeader";

interface PropSetHeaderContainerProps {
  className?: string;
}

const PropSetHeaderContainer: SFC<PropSetHeaderContainerProps> = ({
  className
}) => {
  return <PropSetHeader className={className} />;
};

const mapStateToProps = ({ currentPrototype }) => ({
  selectedPropSet: currentPrototype && currentPrototype.selectedPropSet
});

const mapDispatchToProps = {};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(PropSetHeaderContainer);
