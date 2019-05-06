import {
    SET_PREVIEW_MARKUP
} from '../actions/previewMarkup';

const setPreviewMarkup = (state = '', action) => (action.previewMarkup);

const functionMapper = {
    [SET_PREVIEW_MARKUP]: setPreviewMarkup
}

function previewMarkupReducer(state = true, action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default previewMarkupReducer;