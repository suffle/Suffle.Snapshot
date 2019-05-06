import {
    REQUEST_PROTOTYPE_DATA,
    SET_CURRENT_PROTOTYPE,
    SET_PROTOTYPE_DATA,
    SET_CURRENT_PROPSET,
    SET_PROTOTYPE_LOADING_STATE
} from '../actions/prototype';

const setCurrentPrototype = (state, action) => ({
    ...state,
    name: action.prototypeName,
    data: null
});

const requestPrototypeData = (state) => ({
    ...state,
    data: null,
});

const setPrototypeData = (state, {prototypeData}) => ({
    ...state,
    data: prototypeData,
});

const setCurrentPropSet = (state, {propSetName}) => ({
    ...state,
    currentPropSet: propSetName
})

const setLoadingState = (state, {loading}) => ({
    ...state,
    loading
})

const functionMapper = {
    [SET_CURRENT_PROTOTYPE]: setCurrentPrototype,
    [REQUEST_PROTOTYPE_DATA]: requestPrototypeData,
    [SET_PROTOTYPE_DATA]: setPrototypeData,
    [SET_CURRENT_PROPSET]: setCurrentPropSet,
    [SET_PROTOTYPE_LOADING_STATE]: setLoadingState
}

function prototypeReducer(state = {}, action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default prototypeReducer;