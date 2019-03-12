import {
    SET_CURRENT_SITE_PACKAGE_KEY,
    SET_AVAILABLE_SITE_PACKAGE_KEYS
} from '../actions/packages';

const setAvailableSitePackageKeys = (state, action) =>({
    ...state,
    availableSitePackageKeys: action.data
})

const setCurrentSitePackageKey = (state, action ) => ({
    ...state,
    currentSitePackageKey: action.sitePackageKey
});

const functionMapper = {
    [SET_CURRENT_SITE_PACKAGE_KEY]: setCurrentSitePackageKey,
    [SET_AVAILABLE_SITE_PACKAGE_KEYS]: setAvailableSitePackageKeys
}

function packagesReducer(state = {currentSitePackageKey: '', availableSitePackageKeys: []}, action) {
    if (functionMapper.hasOwnProperty(action.type)) {
        return functionMapper[action.type](state, action)
    }

    return state
}

export default packagesReducer;