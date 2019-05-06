import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import 'react-gh-like-diff/lib/diff2html.css';
import App from './components/App';
import * as serviceWorker from './serviceWorker';
import getEnvData from './config/env'
import createStore from './config/store';

const rootContainer = document.getElementById('snapshotRoot')
const envData = getEnvData(rootContainer);
const store = createStore(envData);

ReactDOM.render(<App store={store} />, document.getElementById('snapshotRoot'));

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: http://bit.ly/CRA-PWA
serviceWorker.unregister();
