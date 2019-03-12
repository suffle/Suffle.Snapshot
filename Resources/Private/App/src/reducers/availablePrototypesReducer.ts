import {
    SET_SITE_PACKAGE_DATA
} from '../actions/packages';


const setSitePackageData = (state, action) => (action.availablePrototypes);

const functionMapper = {
    [SET_SITE_PACKAGE_DATA]: setSitePackageData
}

function availablePrototypesReducer(state = [], action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default availablePrototypesReducer;