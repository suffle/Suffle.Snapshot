import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import mergeClassNames from 'classnames';

class ListPreviewElement extends PureComponent {
    static propTypes = {
        icon: PropTypes.string,
        className: PropTypes.string,
        disabled: PropTypes.bool,
        children: PropTypes.node.isRequired,

        onClick: PropTypes.func,
        isHighlighted: PropTypes.bool,
        onMouseEnter: PropTypes.func,

        theme: PropTypes.shape({/* eslint-disable quote-props */
            'listPreviewElement': PropTypes.string.isRequired,
            'listPreviewElement--isHighlighted': PropTypes.string.isRequired,
            'listPreviewElement--isDisabled': PropTypes.string.isRequired,
            'listPreviewElement__icon': PropTypes.string.isRequired
        }).isRequired, /* eslint-enable quote-props */
        Icon: PropTypes.any.isRequired
    }

    render() {
        const {
            icon,
            className,
            disabled,
            children,

            onClick,
            isHighlighted,
            onMouseEnter,

            theme,
            Icon
        } = this.props;

        const optionClassName = mergeClassNames({
            [theme.listPreviewElement]: true,
            [theme['listPreviewElement--isHighlighted']]: isHighlighted,
            [theme['listPreviewElement--isDisabled']]: disabled,
            [className]: className
        });

        const noop = () => {};

        return (
            <div
                onMouseEnter={disabled ? noop : onMouseEnter}
                onClick={disabled ? noop : onClick}
                className={optionClassName}
                role="button"
                >
                {Boolean(icon) && <div className={theme.listPreviewElement__iconWrapper}><Icon className={theme.listPreviewElement__icon} icon={icon}/></div>}
                {children}

            </div>
        );
    }
}

export default ListPreviewElement;
