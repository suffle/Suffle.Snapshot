import { Config } from "../type/Config";

export const getCurrentSitePackageKey = (state): string => state.sitePackages && state.sitePackages.currentSitePackageKey;
export const getAvailableSitePackageKeys = (state): string[] => state.sitePackages && state.sitePackages.availableSitePackageKeys;
export const getEndpoints = (state): Config.Endpoints => state.endpoints;
export const getSelectedPrototype = (state): string => state.currentPrototype && state.currentPrototype.name;
export const getAvailablePrototypes = (state): string => state.availablePrototypes;
export const getPropSets = (state): string => state.currentPrototype && state.currentPrototype.data;
export const getCurrentPropSet = (state): string => state.currentPrototype && state.currentPrototype.currentPropSet;