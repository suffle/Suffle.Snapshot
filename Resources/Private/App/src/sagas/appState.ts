import { put, select, takeLatest } from "redux-saga/effects";
import axios from 'axios';

import { INITIALIZE_APP } from "../actions/appState";
import { SET_LOADING_STATE } from "../actions/loadingState";
import { getEndpoints, getCurrentSitePackageKey, getAvailableSitePackageKeys } from './selectors';
import { SET_AVAILABLE_SITE_PACKAGE_KEYS, SET_CURRENT_SITE_PACKAGE_KEY } from "../actions/packages";



function* getAllSitePackageKeys() {
    const endpoints = yield select(getEndpoints);

    try {
        const sitePackageKeys = yield axios.get(endpoints.sitePackagesEndpoint);

        yield put({type: SET_AVAILABLE_SITE_PACKAGE_KEYS, data: sitePackageKeys.data || []});
    } catch(error) {
        yield put({type: SET_AVAILABLE_SITE_PACKAGE_KEYS, data: []});
        console.error('Fetch site package key error', error);
    }
}

function* setCurrentSitePackageKey() {
    const selectedSitePackageKey = yield select(getCurrentSitePackageKey);
    const availableSitePackageKeys = yield select(getAvailableSitePackageKeys);
    let sitePackageKey;

    if (selectedSitePackageKey && availableSitePackageKeys.indexOf(selectedSitePackageKey) > -1) {
        sitePackageKey = selectedSitePackageKey;
    } else {
        sitePackageKey = availableSitePackageKeys && availableSitePackageKeys[0];
    }

    yield put({type: SET_CURRENT_SITE_PACKAGE_KEY, sitePackageKey})
}

function* initializeApp() {
    yield put({type: SET_LOADING_STATE, loadingState: true});
    yield getAllSitePackageKeys();
    yield setCurrentSitePackageKey();
};

export default function* watchAppState() {
  yield takeLatest(INITIALIZE_APP, initializeApp);
}
