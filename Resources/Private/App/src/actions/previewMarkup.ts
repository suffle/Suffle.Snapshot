
export const SET_PREVIEW_MARKUP = 'SET_PREVIEW_MARKUP';

export const setPreviewMarkup= (previewMarkup: string) => {
    return {
        type: SET_PREVIEW_MARKUP,
        previewMarkup
    }
};
