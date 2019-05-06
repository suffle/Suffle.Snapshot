/* eslint-disable camelcase, react/jsx-pascal-case */
import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import omit from 'lodash.omit';

class SelectBox_Header extends PureComponent {
    static propTypes = {
        // API with SelectBox
        option: PropTypes.shape({
            icon: PropTypes.string,
            label: PropTypes.string.isRequired,
            value: PropTypes.string.isRequired
        }),
        disabled: PropTypes.bool,

        /* ------------------------------
         * Theme & Dependencies
         * ------------------------------ */
        theme: PropTypes.shape({
            selectBoxHeader: PropTypes.string.isRequired,
            selectBoxHeader__icon: PropTypes.string.isRequired,
            selectBoxHeader__label: PropTypes.string.isRequired
        }).isRequired,
        Icon: PropTypes.any.isRequired,
        IconButton: PropTypes.any.isRequired,
        ListPreviewElement: PropTypes.any.isRequired
    }

    render() {
        const {
            option,
            theme,
            ListPreviewElement,
            disabled
        } = this.props;

        const label = option ? option.label : '';
        const icon = option && option.icon ? option.icon : '';
        const restProps = omit(this.props, ['showResetButton, IconButton']);

        return (
            <div className={theme.selectBoxHeader}>
                <div className={theme.selectBoxHeader__innerPreview}>
                    {option ? <ListPreviewElement
                        {...restProps}
                        label={label}
                        icon={icon}
                        disabled={disabled}
                        /> : <div className={theme.selectBoxHeader__label}>{label}</div>}
                </div>
            </div>
        );
    }
}

export default SelectBox_Header;
