import { Packages } from "../type/SitePackages";
import { Config } from "../type/Config";
import { Prototype } from "../type/Prototype";

const getInitialState = ({
    loginEndpoint,
    overviewUri,
    currentSitePackageKey,
    snapshotObjectsEndpoint,
    snapshotDataEndpoint,
    sitePackagesEndpoint}: Config.Env): {
        endpoints: Config.Endpoints,
        sitePackages: Packages.SitePackages,
        currentPrototype: Prototype.Prototype,
        availablePrototypes: string[],
        loading: boolean
    } => {
        return {
            endpoints: {
                snapshotObjectsEndpoint,
                snapshotDataEndpoint,
                sitePackagesEndpoint,
                loginEndpoint
            },
            sitePackages: {
                currentSitePackageKey: currentSitePackageKey,
                availableSitePackageKeys: []
            },
            currentPrototype: {
                name: '',
                data: null,
                loading: false,
            },
            availablePrototypes: [],
            loading: true
        }

}

export default getInitialState;