import { createStore, compose, applyMiddleware } from "redux";
import createSagaMiddleware from "redux-saga";
import logger from "redux-logger";

import getInitialState from "./initialState";
import rootReducer from "../reducers";
import rootSaga from "../sagas";
import { Config } from "../type/Config";

const composeEnhancers =
  (<any>window).__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;

export default (envData: Config.Env) => {
  const sagaMiddleware = createSagaMiddleware();
  const loggerMiddleware =
    process && process.env.NODE_ENV !== "production" ? logger : null;
  const middleware = [sagaMiddleware, loggerMiddleware].filter(Boolean);
  const initialState = getInitialState(envData);

  const store = createStore(
    rootReducer,
    initialState,
    composeEnhancers(applyMiddleware(...middleware))
  );

  sagaMiddleware.run(rootSaga);

  return store;
};
