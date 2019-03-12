
export declare namespace Prototype {
    type Prototype = {
        name: string,
        data?: PrototypeData
        loading: boolean
    }

    type PrototypeData = {
        [key:string]: PropSet
    }

    type PropSet = {
        snapshot: string,
        current: string,
        hasSnapshot: boolean,
        testSuccess: boolean
    }
}

