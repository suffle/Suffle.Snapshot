import {themr} from '@friendsofreactjs/react-css-themr';
import identifiers from '../identifiers';
import style from './style.css';
import ListPreviewElement from './listPreviewElement';

import injectProps from './../_lib/injectProps';
import Icon from './../Icon';

const ThemedListPreviewElement = themr(identifiers.listPreviewElement, style)(ListPreviewElement);

export default injectProps({
    Icon
})(ThemedListPreviewElement);

