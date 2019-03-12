import {
    SET_LOADING_STATE
} from '../actions/loadingState';

const setLoadingState = (state = false, action) => (action.loadingState);

const functionMapper = {
    [SET_LOADING_STATE]: setLoadingState
}

function loadingStateReducer(state = true, action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default loadingStateReducer;