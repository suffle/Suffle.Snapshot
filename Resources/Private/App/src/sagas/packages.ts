import { takeLatest, put, select } from "redux-saga/effects";
import axios from 'axios';

import { SET_CURRENT_SITE_PACKAGE_KEY, SET_SITE_PACKAGE_DATA } from "../actions/packages";
import { SET_LOADING_STATE } from "../actions/loadingState";
import { getEndpoints, getCurrentSitePackageKey, getSelectedPrototype, getAvailablePrototypes } from "./selectors";
import { SET_CURRENT_PROTOTYPE } from "../actions/prototype";

function* getAllPrototypes(sitePackageKey: string) {
    const endpoints = yield select(getEndpoints);

    try {
        const request = yield axios.get(`${endpoints.snapshotObjectsEndpoint}?packageKey=${sitePackageKey}`);
        const availablePrototypes = request && request.data || [];

        yield put({type: SET_SITE_PACKAGE_DATA, availablePrototypes});
    } catch(error) {
        yield put({type: SET_SITE_PACKAGE_DATA, availablePrototypes: []});
        console.error('Fetch prototypes error', error);
    }
}

function* setCurrentPrototype() {
    const selectedPrototype = yield select(getSelectedPrototype);
    const availablePrototypes = yield select(getAvailablePrototypes);

    let prototypeName;

    if (selectedPrototype && availablePrototypes.indexOf(selectedPrototype) > -1) {
        prototypeName = selectedPrototype;
    } else {
        prototypeName = availablePrototypes && availablePrototypes[0];
    }

    yield put({type: SET_CURRENT_PROTOTYPE, prototypeName})
}

function* setSitePackageData(action) {
    yield put({type: SET_LOADING_STATE, loadingState: true});
    yield getAllPrototypes(action.sitePackageKey);
    yield setCurrentPrototype();
    yield put({type: SET_LOADING_STATE, loadingState: false});
};

export default function* watchPackageChange() {
  yield takeLatest(SET_CURRENT_SITE_PACKAGE_KEY, setSitePackageData);
}
