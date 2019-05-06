/* eslint-disable camelcase, react/jsx-pascal-case */
import SelectBox_ListPreview from './selectBox_ListPreview';

//
// Dependency injection
//
import injectProps from './../_lib/injectProps';
import SelectBox_ListPreviewFlat from './../SelectBox_ListPreviewFlat';

export default injectProps({
    SelectBox_ListPreviewFlat
})(SelectBox_ListPreview);
