import React, { SFC } from "react";

import MarkupRenderer from "../MarkupRenderer";
import { Prototype } from "../../type/Prototype";

import style from "./style.css";

interface RenderViewProps {
  propSet: Prototype.PropSet;
}

const RenderView: SFC<RenderViewProps> = ({ propSet }) => {
  return (
    <div>
      {propSet && propSet.snapshot ? (
        <div>
          <h3 className={style.headline}>Saved Snapshot</h3>
          <div className={style.renderContainer}>
            <div className={style.ratioContainer}>
                <MarkupRenderer
                className={style.renderView}
                previewMarkup={propSet.snapshot}
                />
            </div>
          </div>
        </div>
      ) : null}

      {propSet && propSet.current ? (
        <div>
          <h3 className={style.headline}>Current Rendering</h3>
          <div className={style.renderContainer}>
            <div className={style.ratioContainer}>
                <MarkupRenderer
                className={style.renderView}
                previewMarkup={propSet.current}
                />
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
};

export default RenderView;
