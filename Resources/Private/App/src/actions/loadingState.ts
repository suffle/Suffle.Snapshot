
export const SET_LOADING_STATE = 'SET_LOADING_STATE';

export const setLoadingState = (loadingState: boolean) => {
    return {
        type: SET_LOADING_STATE,
        loadingState
    }
};
