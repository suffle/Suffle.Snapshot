import { Config } from "../type/Config";

export default el => {
    const {
        loginEndpoint,
        overviewUri,
        currentSitePackageKey,
        snapshotObjectsEndpoint,
        snapshotDataEndpoint,
        sitePackagesEndpoint
    }: Config.Env = el.dataset;

    return {
        loginEndpoint,
        overviewUri,
        currentSitePackageKey,
        snapshotObjectsEndpoint,
        snapshotDataEndpoint,
        sitePackagesEndpoint
    };
};
