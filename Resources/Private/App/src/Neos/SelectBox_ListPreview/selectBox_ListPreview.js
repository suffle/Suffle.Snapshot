/* eslint-disable camelcase, react/jsx-pascal-case */
import React, { PureComponent } from "react";
import PropTypes from "prop-types";

class SelectBox_ListPreview extends PureComponent {
  static propTypes = {
    // For explanations of the PropTypes, see SelectBox.js
    options: PropTypes.arrayOf(PropTypes.shape({})),
    theme: PropTypes.object,

    // Dependency injection
    SelectBox_ListPreviewFlat: PropTypes.any.isRequired
  };

  render() {
    const { SelectBox_ListPreviewFlat } = this.props;

    const ListPreviewComponent = SelectBox_ListPreviewFlat;

    // TODO: replace horible self-made I18n replace
    return <ListPreviewComponent {...this.props} />;
  }
}

export default SelectBox_ListPreview;
