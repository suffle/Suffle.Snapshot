<?php

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

use Neos\Flow\Annotations as Flow;
use \Neos\Neos\Domain\Service\FusionService as NeosFusionService;


class FusionService extends NeosFusionService
{
    const RENDERPATH_DISCRIMINATOR = 'snapshotPrototypeRenderer_';

    /**
     * @Flow\InjectConfiguration(path="fusion.autoInclude", package="Neos.Neos")
     * @var array
     */
    protected $autoIncludeConfiguration = array();

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;

    /**
     * Returns a merged fusion object tree in the context of the given site-package
     *
     * @param string $siteResourcesPackageKey
     * @return array The merged object tree as of the given node
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     */
    public function getMergedFusionObjectTreeForSitePackage(string $siteResourcesPackageKey): array
    {
        $site = $this->siteRepository->findDefault();
        return $this->createFusionConfigurationFromSite($site)->toArray();
    }

    /**
     * Add snapshot rendering configuration to the fusion-ast
     *
     * @param array $fusionAst
     * @return array
     */
    protected function addSnapshotPrototypesToFusionAst(array $fusionAst): array
    {
        $snapshotPrototypeConfigurations = [];
        $snapshotRenderingPrototypes = [];
        $snapshotRenderingProps = [];

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
            if (array_key_exists('props', $prototypeConfiguration['__meta'][$annotationKey])
                && is_array($prototypeConfiguration['__meta'][$annotationKey]['props'])) {
                $snapshotRenderingProps[$prototypeName] = $prototypeConfiguration['__meta'][$annotationKey]['props'];
            }
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
     *
     * @param array $fusionAst
     * @return array
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
                    list($prototypeVendor, $prototypeName) = explode(':', $prototypeFullName, 2);
                    $snapshotConfiguration = $prototypeObject['__meta'][$annotationKey];
                    $snapshotObjects[$prototypeFullName] = [
                        'title' => (isset($snapshotConfiguration['title'])) ? $snapshotConfiguration['title'] : implode(' ', array_reverse(explode('.', $prototypeName))),
                        'path' => (isset($snapshotConfiguration['path'])) ? $snapshotConfiguration['path'] : $prototypeName,
                        'description' => (isset($snapshotConfiguration['description'])) ? $snapshotConfiguration['description'] : '',
                        'options' => (isset($snapshotConfiguration['options'])) ? $snapshotConfiguration['options'] : null,
                    ];
                }
            }
        }
        return $snapshotObjects;
    }

    /**
     * Returns a list of testable prototypes
     *
     * @param string $siteResourcesPackageKey
     * @return array Array of prototype names to test
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     */
    public function getPrototypeNamesForTesting(string $siteResourcesPackageKey): array
    {
        $siteRootFusionPathAndFilename = sprintf($this->siteRootFusionPattern, $siteResourcesPackageKey);

        $mergedFusionCode = '';
        $mergedFusionCode .= $this->generateNodeTypeDefinitions();
        $mergedFusionCode .= $this->getFusionIncludes($this->prepareAutoIncludeFusion());
        $mergedFusionCode .= $this->getFusionIncludes($this->prependFusionIncludes);
        $mergedFusionCode .= $this->readExternalFusionFile($siteRootFusionPathAndFilename);
        $mergedFusionCode .= $this->getFusionIncludes($this->appendFusionIncludes);

        $fusionAst = $this->fusionParser->parse($mergedFusionCode, $siteRootFusionPathAndFilename);
        $prototypeNames = $this->filterSnapshotPrototypes($fusionAst);

        return $prototypeNames;
    }

    /**
     * Add snapshot rendering configuration to the fusion-ast
     *
     * @param array $fusionAst
     * @return array
     */
    protected function filterSnapshotPrototypes(array $fusionAst): array
    {
        $snapshotPrototypeConfigurations = [];
        $prototypesList = [];
        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';

        foreach ($fusionAst['__prototypes'] as $prototypeName => $prototypeConfiguration) {
            if (array_key_exists('__meta', $prototypeConfiguration)
                && array_key_exists($annotationKey, $prototypeConfiguration['__meta'])
            ) {
                $snapshotPrototypeConfigurations[$prototypeName] = $prototypeConfiguration;
                array_push($prototypesList, $prototypeName);
            }
        }


        return $prototypesList;
    }
}
