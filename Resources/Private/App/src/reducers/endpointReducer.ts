const functionMapper = {}

function loadingStateReducer(state = {}, action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default loadingStateReducer;