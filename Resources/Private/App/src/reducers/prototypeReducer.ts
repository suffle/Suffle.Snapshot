import {
    REQUEST_PROTOTYPE_DATA,
    SET_CURRENT_PROTOTYPE,
    SET_PROTOTYPE_DATA
} from '../actions/prototype';

const setCurrentPrototype = (state, action) => ({
    ...state,
    name: action.prototypeName,
    data: null
});

const requestPrototypeData = (state) => ({
    ...state,
    data: null,
    loading: true
});

const setPrototypeData = (state, action) => ({
    ...state,
    data: action.prototypeData,
    loading: false
});

const functionMapper = {
    [SET_CURRENT_PROTOTYPE]: setCurrentPrototype,
    [REQUEST_PROTOTYPE_DATA]: requestPrototypeData,
    [SET_PROTOTYPE_DATA]: setPrototypeData
}

function prototypeReducer(state = {}, action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default prototypeReducer;