<?php

declare(strict_types=1);

namespace Suffle\Snapshot\Fusion;

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

use DomainException;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\FusionService as NeosFusionService;

class FusionService extends NeosFusionService
{
    public const RENDERPATH_DISCRIMINATOR = 'snapshotPrototypeRenderer_';

    #[Flow\InjectConfiguration]
    protected ?array $settings;

    /**
     * Returns a merged fusion object tree in the context of the given site-package
     *
     * @return array The merged object tree as of the given node
     * @throws DomainException
     */
    public function getMergedFusionObjectTreeForSitePackage(string $siteResourcesPackageKey): array
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $site = $this->siteRepository->findOneBySiteResourcesPackageKey($siteResourcesPackageKey);
        if (!$site) {
            throw new DomainException("No site found for package key " . $siteResourcesPackageKey, 1733996912);
        }
        return $this->createFusionConfigurationFromSite($site)->toArray();
    }

    /**
     * Add snapshot rendering configuration to the fusion-ast
     */
    protected function addSnapshotPrototypesToFusionAst(array $fusionAst): array
    {
        $snapshotPrototypeConfigurations = [];
        $snapshotRenderingPrototypes = [];

        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';

        foreach ($fusionAst['__prototypes'] as $prototypeName => $prototypeConfiguration) {
            if (array_key_exists('__meta', $prototypeConfiguration)
                && array_key_exists($annotationKey, $prototypeConfiguration['__meta'])
            ) {
                $snapshotPrototypeConfigurations[$prototypeName] = $prototypeConfiguration;
            }
        }

        // create rendering prototypes with dummy data
        foreach ($snapshotPrototypeConfigurations as $prototypeName => $prototypeConfiguration) {
            $renderPrototypeFusion = [
                '__objectType' => $prototypeName,
                '__value' => null,
                '__eelExpression' => null
            ];
            $snapshotRenderingPrototypes[$prototypeName] = $renderPrototypeFusion;
        }

        // create render pathes
        foreach ($snapshotRenderingPrototypes as $prototypeName => $prototypeConfiguration) {
            $key = self::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'], $prototypeName);
            $fusionAst[$key] = $prototypeConfiguration;
        }

        return $fusionAst;
    }

    /**
     * Get all snapshot objects for the given fusion-ast
     */
    public function getSnapshotObjectsFromFusionAst(array $fusionAst): array
    {
        $snapshotObjects = [];
        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';

        if ($fusionAst && $fusionAst['__prototypes']) {
            foreach ($fusionAst['__prototypes'] as $prototypeFullName => $prototypeObject) {
                if (array_key_exists('__meta', $prototypeObject)
                    && is_array($prototypeObject['__meta'])
                    && array_key_exists($annotationKey, $prototypeObject['__meta'])) {
                    [, $prototypeName] = explode(':', $prototypeFullName, 2);
                    $snapshotConfiguration = $prototypeObject['__meta'][$annotationKey];
                    $snapshotObjects[$prototypeFullName] = [
                        'title' => $snapshotConfiguration['title'] ?? implode(' ',
                                array_reverse(explode('.', $prototypeName))),
                        'path' => $snapshotConfiguration['path'] ?? $prototypeName,
                        'description' => $snapshotConfiguration['description'] ?? '',
                        'options' => $snapshotConfiguration['options'] ?? null,
                    ];
                }
            }
        }
        return $snapshotObjects;
    }

    /**
     * Returns a list of testable prototypes
     *
     * @return array Array of prototype names to test
     */
    public function getPrototypeNamesForTesting(string $siteResourcesPackageKey): array
    {
        $fusionAst = $this->getMergedFusionObjectTreeForSitePackage($siteResourcesPackageKey);
        return $this->filterSnapshotPrototypes($fusionAst);
    }

    /**
     * Add snapshot rendering configuration to the fusion-ast
     */
    protected function filterSnapshotPrototypes(array $fusionAst): array
    {
        $prototypesList = [];
        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';
        $excludedPackageKeys = $this->settings['exclude']['packageKeys'] ?? [];

        foreach ($fusionAst['__prototypes'] as $prototypeName => $prototypeConfiguration) {
            $prototypePackageKey = explode(':', $prototypeName)[0];
            if (array_key_exists('__meta', $prototypeConfiguration)
                && array_key_exists($annotationKey, $prototypeConfiguration['__meta'])
                && !in_array($prototypePackageKey, $excludedPackageKeys, true)
            ) {
                $prototypesList[] = $prototypeName;
            }
        }
        return $prototypesList;
    }
}
