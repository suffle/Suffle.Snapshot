import React, { SFC } from "react";

interface MarkupRendererProps {
  className?: string;
  markup: string;
  loading: boolean;
  setLoadingState(loading: boolean): void;
}

const MarkupRenderer: SFC<MarkupRendererProps> = ({
  className,
  loading,
  setLoadingState,
  markup
}) => {
  const iframe = document.createElement("iframe");

  const onFinishedLoading = () => setLoadingState(false);

  if ("srcdoc" in iframe) {
    return (
      <>
        <iframe
          className={className}
          srcDoc={markup}
          onLoad={onFinishedLoading}
        />
        {loading ? <span>Loading</span> : null}
      </>
    );
  } else {
    return (
      <>
        <iframe
          className={className}
          src={`javascript: '${markup}'`}
          onLoad={onFinishedLoading}
        />
        {loading ? <span>Loading</span> : null}
      </>
    );
  }
};

export default MarkupRenderer;
