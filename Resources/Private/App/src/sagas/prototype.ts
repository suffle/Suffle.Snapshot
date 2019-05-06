import { takeLatest, put, select } from "redux-saga/effects";
import axios from 'axios';

import { getEndpoints, getCurrentSitePackageKey, getPropSets, getCurrentPropSet } from "./selectors";
import { SET_CURRENT_PROTOTYPE, REQUEST_PROTOTYPE_DATA, SET_PROTOTYPE_DATA, SET_CURRENT_PROPSET, SET_PROTOTYPE_LOADING_STATE } from "../actions/prototype";

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

function* setCurrentPropSet() {
    const propSets = yield select(getPropSets);
    const currentPropSet = yield select(getCurrentPropSet);

    if (!propSets) {
        yield put({type: SET_CURRENT_PROPSET, propSetName: ''});
        return;
    }

    if (!currentPropSet || !propSets.hasOwnProperty(currentPropSet)) {
        const newPropSet = propSets.hasOwnProperty('default') ? 'default' : Object.keys(propSets)[0];
        yield put({type: SET_CURRENT_PROPSET, propSetName: newPropSet});
    }
}

function* setPrototypeData(action) {
    yield put({type: SET_PROTOTYPE_LOADING_STATE, loading: true});
    yield put({type: REQUEST_PROTOTYPE_DATA});
    yield getPrototypeData(action.prototypeName);
    yield setCurrentPropSet();
    yield put({type: SET_PROTOTYPE_LOADING_STATE, loading: false});

};

export default function* watchPrototypeChange() {
  yield takeLatest(SET_CURRENT_PROTOTYPE, setPrototypeData);
}
