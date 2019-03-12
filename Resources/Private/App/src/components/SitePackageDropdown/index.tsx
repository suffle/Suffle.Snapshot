import React, { SFC } from "react";
import { connect } from "react-redux";

import { setCurrentSitePackageKey } from "../../actions/packages";
import SitePackageDropdown from "./SitePackageDropdown";

interface SitePackageDropdownContainerProps {
  availableSitePackages: string[];
  currentSitePackage: string;
  updateSitePackage(sitePackageKey): void;
}

const SitePackageDropdownContainer: SFC<SitePackageDropdownContainerProps> = ({
  availableSitePackages,
  currentSitePackage,
  updateSitePackage
}) => {
  const onSiteChange = value => {
    updateSitePackage(value);
  };

  return (
    <SitePackageDropdown
      options={availableSitePackages}
      selected={currentSitePackage}
      onSiteChange={onSiteChange}
    />
  );
};

const mapStateToProps: object = ({ sitePackages }) => ({
  availableSitePackages: sitePackages && sitePackages.availableSitePackageKeys,
  currentSitePackage: sitePackages && sitePackages.currentSitePackageKey
});

const mapDispatchToProps = {
  updateSitePackage: setCurrentSitePackageKey
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(SitePackageDropdownContainer);
