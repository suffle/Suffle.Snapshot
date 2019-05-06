/* eslint-disable camelcase, react/jsx-pascal-case */
import {themr} from '@friendsofreactjs/react-css-themr';
import keydown from 'react-keydown';
import identifiers from '../identifiers';
import style from './style.css';
import {keys} from './config';
import SelectBox from './selectBox';
import injectProps from './../_lib/injectProps';
import DropDown from './../DropDown';
import SelectBox_Header from './../SelectBox_Header';
import SelectBox_ListPreview from './../SelectBox_ListPreview';

const ThemedSelectBox = themr(identifiers.selectBox, style)(SelectBox);
const WithKeys = keydown(keys)(ThemedSelectBox);


export default injectProps({
    DropDown,
    SelectBox_Header,
    SelectBox_ListPreview
})(WithKeys);
