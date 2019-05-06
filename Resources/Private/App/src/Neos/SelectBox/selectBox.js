/* eslint-disable camelcase, react/jsx-pascal-case */
import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import SelectBox_Option_SingleLine from '../SelectBox_Option_SingleLine';
import mergeClassNames from 'classnames';
import isEqual from 'lodash.isequal';

// TODO: document component usage && check code in detail
export default class SelectBox extends PureComponent {
    static defaultProps = {
        options: [],
        scrollable: true,
        showDropDownToggle: true,
        ListPreviewElement: SelectBox_Option_SingleLine
    };

    static propTypes = {
        // ------------------------------
        // Basic Props for core functionality
        // ------------------------------
        /**
         * This prop represents the set of options to be chosen from
         * Each option must have a value and can have a label and an icon.
         */
        options: PropTypes.arrayOf(
            PropTypes.shape({
                icon: PropTypes.string,
                // "value" is not part of PropTypes validation, as the "value field" is specified via the "optionValueField" property
                label: PropTypes.oneOfType([
                    PropTypes.string,
                    PropTypes.object
                ]).isRequired,
                disabled: PropTypes.bool
            })
        ),

        /**
         * Additional className wich will be applied
         */
        className: PropTypes.string,

        /**
         * This prop represents the currently selected value.
         */
        value: PropTypes.any,

        /**
         * This prop gets called when an option was selected. It returns the new value.
         */
        onValueChange: PropTypes.func.isRequired,

        // ------------------------------
        // Visual customization of the Select Box
        // ------------------------------

        /**
         * Shows dropdown toggle. Set by default. Useful in components that display search, where you don't want to let the user manually controll the collapsing of selectbox
         */
        showDropDownToggle: PropTypes.bool,

        /**
         * Limit height and show scrollbars if needed, defaults to true
         */
        scrollable: PropTypes.bool,

        /**
         * Component used for rendering the individual option elements; Usually this component uses "ListPreviewElement" internally for common styling.
         */
        ListPreviewElement: PropTypes.any,


        // ------------------------------
        // Theme & Dependencies
        // ------------------------------
        theme: PropTypes.shape({/* eslint-disable quote-props */
            'selectBox__btn--noRightPadding': PropTypes.string
        }).isRequired, /* eslint-enable quote-props */

        DropDown: PropTypes.any.isRequired,
        SelectBox_Header: PropTypes.any.isRequired,
        SelectBox_ListPreview: PropTypes.any.isRequired
    };

    state = {
        isExpanded: false,
        focusedValue: ''
    };

    render() {
        const {
            options,
            theme,
            showDropDownToggle,
            ListPreviewElement,
            disabled,
            className,
            DropDown,
            SelectBox_ListPreview,
            value
        } = this.props;

        const {focusedValue} = this.state;
        const canOpen = !disabled && (options.filter(option => option.value !== value).length > 0);
        const isExpanded = !canOpen ? false : this.state.isExpanded;

        const headerClassName = mergeClassNames({
            [className]: true,
            [theme.selectBox__btn]: true,
            [theme['selectBox__btn--noRightPadding']]: !canOpen,
            [theme['selectBox--disabled']]: !canOpen
        });


        return (
            <DropDown.Stateless className={theme.selectBox} isOpen={isExpanded} onToggle={this.handleToggleExpanded} onClose={this.handleClose}>
                <DropDown.Header className={headerClassName} shouldKeepFocusState={false} showDropDownToggle={showDropDownToggle && canOpen}>
                    {this.renderHeader()}
                </DropDown.Header>
                <DropDown.Contents className={theme.selectBox__contents} scrollable={true}>
                    <ul className={theme.selectBox__list}>
                        <SelectBox_ListPreview
                            {...this.props}

                            theme={theme}
                            ListPreviewElement={ListPreviewElement}
                            focusedValue={focusedValue}
                            onChange={this.handleChange}
                            onOptionFocus={this.handleOptionFocusChange}
                            />
                    </ul>
                </DropDown.Contents>
            </DropDown.Stateless>
        );
    }

    renderHeader() {
        const {
            options,
            value,
            SelectBox_Header
        } = this.props;

        // Compare selected value less strictly: allow loose comparision and deep equality of objects
        const selectedOption = options.find(option => option.value == value || isEqual(option.value, value)); // eslint-disable-line eqeqeq

        return (
            <SelectBox_Header
                {...this.props}
                option={selectedOption}
                />
        );
    }

    handleChange = option => {
        this.props.onValueChange(option.value);
    }

    handleToggleExpanded = () => {
        // Return earyl if disabled
        if (this.props.disabled) {
            return;
        }

        let isExpanded;
        if (this.props.displaySearchBox) {
            if (this.props.value) {
                isExpanded = true;
            } else {
                // Force expanded dropdown unless has showDropDownToggle (e.g. for nodetypes filter in the PageTree)
                isExpanded = this.props.showDropDownToggle ? !this.state.isExpanded : true;
            }
        } else {
            // If simple SelectBox, just toggle it
            isExpanded = !this.state.isExpanded;
        }
        this.setState({
            isExpanded
        });
    }

    handleClose = () => {
        this.setState({
            isExpanded: false
        });
    }

    handleOptionFocusChange = option => {
        this.setState({
            focusedValue: option.value
        });
    }

    /**
     * Keyboard handling
     */
    componentWillReceiveProps({keydown}) {
        this.handleKeyDown(keydown.event);
    }

    handleKeyDown = e => {
        const {options} = this.props;

        if (this.state.isExpanded && e && ['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(e.key)) {
            // Do not scroll while we are doing keyboard interaction
            e.preventDefault();

            const currentIndex = options.findIndex(option => option.value === this.state.focusedValue);

            if (e.key === 'ArrowDown') {
                const newIndex = currentIndex + 1 >= options.length ? currentIndex : currentIndex + 1;
                this.setState({
                    focusedValue: options[newIndex].value
                });
            } else if (e.key === 'ArrowUp') {
                const newIndex = currentIndex - 1 < 0 ? 0 : currentIndex - 1;
                this.setState({
                    focusedValue: options[newIndex].value
                });
            } else if (e.key === 'Enter') {
                if (currentIndex < options.length && currentIndex >= 0) {
                    this.handleChange(options[currentIndex]);
                }

                this.setState({
                    isExpanded: false
                });
            } else if (e.key === 'Escape') {
                this.setState({
                    focusedValue: '',
                    isExpanded: false
                });
            }
        }
    }
}
