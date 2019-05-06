import React, { SFC } from "react";
import { connect } from "react-redux";

import RenderView from "./RenderView";
import { Prototype } from "../../type/Prototype";

interface RenderViewContainerProps {
  className?: string;
  propSet: Prototype.PropSet;
}

const RenderViewContainer: SFC<RenderViewContainerProps> = props => {
  return <RenderView {...props} />;
};

const mapStateToProps = ({ currentPrototype }) => {
  const propSets = currentPrototype && currentPrototype.data;
  const selectedPropSet = currentPrototype && currentPrototype.currentPropSet;

  return {
    propSet:
      propSets &&
      propSets.hasOwnProperty(selectedPropSet) &&
      propSets[selectedPropSet]
  };
};

export default connect(mapStateToProps)(RenderViewContainer);
