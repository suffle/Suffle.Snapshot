import { all } from "redux-saga/effects";
import watchPackageChange from "./packages";
import watchAppState from "./appState";
import watchPrototypeChange from "./prototype";

export default function* rootSaga() {
  yield all([watchAppState(), watchPackageChange(), watchPrototypeChange()]);
}
