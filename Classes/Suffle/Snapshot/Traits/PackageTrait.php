<?php

namespace Suffle\Snapshot\Traits;

/**
 * This file is part of the Suffle.Snapshot package
 *
 * (c) 2018
 * sebastian Flor <sebastian@flor.rocks>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;

/**
 * Utility trait to determine package keys
 */
trait PackageTrait
{

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    /**
     * Determine the default site package key
     *
     * @return string
     */
    protected function getDefaultSitePackageKey(): string
    {
        $sitePackageKeys = $this->getActiveSitePackageKeys();
        return reset($sitePackageKeys);
    }

    /**
     * Get a list of all active site package keys
     * @return array
     */
    protected function getActiveSitePackageKeys(): array
    {
        $sitePackages = $this->packageManager->getFilteredPackages('available', null, 'neos-site');
        $result = [];
        foreach ($sitePackages as $sitePackage) {
            $packageKey = $sitePackage->getPackageKey();
            $result[] = $packageKey;
        }
        return $result;
    }
}
