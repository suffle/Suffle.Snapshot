import React, { useEffect } from "react";
import { Provider } from "react-redux";

import SnapshotUi from './SnapshotUi';

import { initializeApp } from '../actions/appState';
import logo from "./logo.svg";
import "./App.css";

export interface AppProps {
  store: any;
}

const App = (props: AppProps) => {
  useEffect(() => {
      props.store.dispatch(initializeApp())
  });

  return (
    <Provider store={props.store}>
        <SnapshotUi />
    </Provider>
  );
};

export default App;
