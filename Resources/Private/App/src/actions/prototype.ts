import { Prototype } from "../type/Prototype";

export const REQUEST_PROTOTYPE_DATA = 'REQUEST_PROTOTYPE_DATA';
export const SET_CURRENT_PROTOTYPE = 'SET_CURRENT_PROTOTYPE';
export const SET_PROTOTYPE_DATA = 'SET_PROTOTYPE_DATA';

export const requestPrototypeData = () => {
    return {
        type: REQUEST_PROTOTYPE_DATA,
    }
}

export const setCurrentPrototype = (prototypeName: string) => {
    return {
        type: SET_CURRENT_PROTOTYPE,
        prototypeName
    }
}

export const setPrototypeData = (prototypeData: Prototype.PrototypeData) => {
    return {
        type: SET_PROTOTYPE_DATA,
        prototypeData
    }
}
