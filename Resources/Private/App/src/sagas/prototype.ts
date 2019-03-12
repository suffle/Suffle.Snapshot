import { takeLatest, put, select } from "redux-saga/effects";
import axios from 'axios';

import { SET_CURRENT_SITE_PACKAGE_KEY, SET_SITE_PACKAGE_DATA } from "../actions/packages";
import { SET_LOADING_STATE } from "../actions/loadingState";
import { getEndpoints, getCurrentSitePackageKey, getSelectedPrototype, getAvailablePrototypes } from "./selectors";
import { SET_CURRENT_PROTOTYPE, REQUEST_PROTOTYPE_DATA, SET_PROTOTYPE_DATA } from "../actions/prototype";

function* getPrototypeData(prototypeName: string) {
    const endpoints = yield select(getEndpoints);
    const sitePackageKey = yield select(getCurrentSitePackageKey);

    try {
        const request = yield axios.get(`${endpoints.snapshotDataEndpoint}?prototypeName=${prototypeName}&packageKey=${sitePackageKey}&`);
        const prototypeData = request && request.data || null;

        yield put({type: SET_PROTOTYPE_DATA, prototypeData});
    } catch(error) {
        yield put({type: SET_PROTOTYPE_DATA, prototypeData: null});
        console.error('Fetch prototypes error', error);
    }
}

function* setPrototypeData(action) {
    yield put({type: REQUEST_PROTOTYPE_DATA, loadingState: true});
    yield getPrototypeData(action.prototypeName);
};

export default function* watchPrototypeChange() {
  yield takeLatest(SET_CURRENT_PROTOTYPE, setPrototypeData);
}
