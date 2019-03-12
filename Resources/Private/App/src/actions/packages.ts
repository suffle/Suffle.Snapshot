export const SET_CURRENT_SITE_PACKAGE_KEY = 'SET_CURRENT_SITE_PACKAGE_KEY';
export const SET_SITE_PACKAGE_DATA = 'SET_SITE_PACKAGE_DATA';
export const SET_AVAILABLE_SITE_PACKAGE_KEYS = 'SET_AVAILABLE_SITE_PACKAGE_KEYS';

export const setCurrentSitePackageKey = (sitePackageKey: string) => {
    return {
        type: SET_CURRENT_SITE_PACKAGE_KEY,
        sitePackageKey
    }
}

export const setAvailableSitePackageKeys = (data: string[]) => {
    return {
        type: SET_AVAILABLE_SITE_PACKAGE_KEYS,
        data
    }
}

export const setSitePackageKeyData = (availablePrototypes: string[]) => {
    return {
        type: SET_CURRENT_SITE_PACKAGE_KEY,
        availablePrototypes
    }
}
