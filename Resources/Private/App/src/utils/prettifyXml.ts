const defaultIndentation = "  ";

const isSelfClosing = tagName => {
  const elem = document.createElement(tagName);
  return elem.outerHTML.indexOf("><") == -1;
};

type BuildTagOptions = {
  attributes?: NamedNodeMap;
  selfClosing?: boolean;
  endTag?: boolean;
};

const generateNewLine: (level: number) => string = level => {
  return "\n" + defaultIndentation.repeat(level);
};

const buildTag: (tagName: string, options: BuildTagOptions) => string = (
  tagName,
  { attributes, selfClosing, endTag }
) => {
  const tagStart = endTag ? "</" : "<";
  const tagEnd = selfClosing ? "/>" : ">";
  const attributesString =
    !endTag && attributes
      ? [...attributes].reduce((accumulator, attr) => {
          const value = attr.value ? `="${attr.value}"` : "";
          return (accumulator += ` ${attr.name}${value}`);
        }, "")
      : "";
  return tagStart + tagName + attributesString + tagEnd;
};

const processNode: (node: any, level: number, newLine?: boolean) => string = (
  node,
  level,
  newLine = true
) => {
  const tagName = node.nodeName && node.nodeName.toLowerCase();
  const lineBreak = newLine ? generateNewLine(level) : "";

  if (node.nodeName === "#text" || node.nodeName === "#comment") {
    return lineBreak + node.textContent;
  } else {
    const childNodes = [...node.childNodes];

    if (isSelfClosing(tagName)) {
      return (
        lineBreak +
        buildTag(tagName, { attributes: node.attributes, selfClosing: true })
      );
    } else {
      const hasTextChildren = childNodes.some(
        child => child.nodeName === "#text"
      );

      const startTag = buildTag(tagName, { attributes: node.attributes });
      const endTag = (hasTextChildren ? '' : lineBreak) +  buildTag(tagName, { endTag: true });
      const childNodeText = childNodes.reduce((accumulator, child) => {
        return (accumulator += processNode(child, level + 1, !hasTextChildren));
      }, "");

      const tagString = startTag + childNodeText  + endTag;

      return lineBreak + tagString;
    }
  }
};

const prettifyXml: (xml: string) => string = xml => {
  const parser = new DOMParser();
  const nodes = parser.parseFromString(xml, "text/html").body.childNodes;

  return [...nodes].reduce((accumulator, childNode) => {
    return (accumulator += processNode(childNode, 0));
  }, "").trim();
};

export default prettifyXml;
