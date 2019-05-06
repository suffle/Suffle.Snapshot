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
use Neos\Fusion\View\FusionView as BaseFusionView;
use Neos\Fusion\Core\Runtime as FusionRuntime;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Service;

/**
 * A specialized fusion view to render snapshots
 */
class FusionView extends BaseFusionView
{
    const RENDERPATH_DISCRIMINATOR = 'snapshotPrototypeRenderer_';

    /**
     * @Flow\Inject
     * @var \Suffle\Snapshot\Fusion\FusionService
     */
    protected $fusionService;

    /**
     * @Flow\Inject
     * @var Service
     */
    protected $i18nService;

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;

    /**
     * Load Fusion from the directories specified by $this->getOption('fusionPathPatterns')
     *
     * @return void
     * @throws \Neos\Flow\Mvc\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     */

    protected function loadFusion(): void
    {
        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($this->getOption('packageKey'));
        $this->parsedFusion = $fusionAst;
    }

    /**
     * @var array
     */
    protected $overriddenPropsPerPrototype = [];

    /**
     * Special method to render a specific prototype and all of its propSets
     *
     * @param string $prototypeName
     * @param array $locales
     * @return array
     * @throws \Exception
     * @throws \Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException
     * @throws \Neos\Flow\Mvc\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     */
    public function renderSnapshotPrototype(string $prototypeName, array $locales = []): array
    {
        if ($locales) {
            $currentLocale = new Locale($locales[0]);
            $this->i18nService->getConfiguration()->setCurrentLocale($currentLocale);
            $this->i18nService->getConfiguration()->setFallbackRule(array('strict' => false, 'order' => array_reverse($locales)));
        }

        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($this->getOption('packageKey'));
        $fusionAstArray = $this->postProcessFusionAstForPrototype($fusionAst, $prototypeName);

        $fusionPath = sprintf('/<%s>', $prototypeName);
        $output = [];

        foreach ($fusionAstArray as $propSetName => $singleFusionAst) {
            $fusionRuntime = new FusionRuntime($singleFusionAst, $this->controllerContext);
            $fusionRuntime->pushContextArray($this->variables);

            try {
                $output[$propSetName] = $fusionRuntime->render($fusionPath);
            } catch (\RuntimeException $exception) {
                throw $exception->getPrevious();
            }

            $fusionRuntime->popContext();
        }

        return $output;
    }

    public function renderPrototype($prototypeName)
    {
        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($this->getOption('packageKey'));
        $fusionPath = sprintf('/<%s>', $prototypeName);
        $fusionRuntime = new FusionRuntime($fusionAst, $this->controllerContext);
        $fusionRuntime->pushContextArray($this->variables);

        try {
            return $fusionRuntime->render($fusionPath);
        } catch (\RuntimeException $exception) {
            throw $exception->getPrevious();
        }
    }

    /**
     * Override props via parameters, props and propSet configuration
     *
     * @param array $fusionAst
     * @param string $prototypeName
     * @return array
     * @throws \Exception
     */

    protected function postProcessFusionAstForPrototype(array $fusionAst, string $prototypeName): array
    {
        $this->assertWellFormedSnapshotObject($fusionAst, $prototypeName);
        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';

        $prototypeBaseConfiguration = $fusionAst['__prototypes'][$prototypeName];
        $snapshotConfiguration = $fusionAst['__prototypes'][$prototypeName]['__meta'][$annotationKey];
        $prototypeConfigurations = [];
        $fusionAstArray = [];

        // Set default prototype configuration
        if (array_key_exists('props', $snapshotConfiguration)) {
            $prototypeDefaultConfiguration = array_replace_recursive(
                $prototypeBaseConfiguration,
                $snapshotConfiguration['props']
            );

        } else {
            $prototypeDefaultConfiguration = $prototypeBaseConfiguration;
        }

        $prototypeConfigurations['default'] = $prototypeDefaultConfiguration;

        // Add configurations for propSets
        if (
        array_key_exists('propSets', $snapshotConfiguration)
        ) {
            foreach ($snapshotConfiguration['propSets'] as $propSetName => $propSet) {
                $propSetPrototypeConfiguration = array_replace_recursive(
                    $prototypeDefaultConfiguration,
                    $propSet
                );

                $prototypeConfigurations[$propSetName] = $propSetPrototypeConfiguration;
            }
        }

        foreach ($prototypeConfigurations as $propSet => $fusionConfiguration) {
            $fusionAstArray[$propSet] = $this->postProcessSingleConfiguration($fusionAst, $fusionConfiguration, $prototypeName);
        }

        return $fusionAstArray;
    }

    /**
     * get fusionAst for single configuration
     *
     * @param array $fusionAst
     * @param $fusionConfiguration
     * @param string $prototypeName
     * @return array
     */
    protected function postProcessSingleConfiguration(array $fusionAst, $fusionConfiguration, string $prototypeName): array
    {
        $prototypeConfiguration = $fusionConfiguration;
        $fusionAst['__prototypes'][$prototypeName] = $prototypeConfiguration;
        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';

        foreach ($fusionAst['__prototypes'] as $otherPrototypeName => &$prototypeConfiguration) {
            if ($otherPrototypeName === $prototypeName) {
                continue;
            }

            if (
                array_key_exists('__meta', $prototypeConfiguration) &&
                array_key_exists($annotationKey, $prototypeConfiguration['__meta']) &&
                array_key_exists('props', $prototypeConfiguration['__meta'][$annotationKey])
            ) {
                $prototypeConfiguration = array_replace_recursive(
                    $prototypeConfiguration,
                    $prototypeConfiguration['__meta'][$annotationKey]['props']
                );
            }
        }

        return $fusionAst;
    }

    /**
     * Make sure, that this prototype is actually configured for being rendered as a snapshot
     *
     * @param array $fusionAst
     * @param string $prototypeName
     * @return void
     * @throws \Exception
     */
    protected function assertWellFormedSnapshotObject(array $fusionAst, string $prototypeName): void
    {
        $annotationKey = $this->settings['annotationKey'] ?: 'snapshot';

        if (!array_key_exists($prototypeName, $fusionAst['__prototypes'])) {
            throw new \Exception(sprintf('Prototype "%s" does not exist.', $prototypeName), 1500825696);
        }

        if (!array_key_exists('__meta', $fusionAst['__prototypes'][$prototypeName])
            || !array_key_exists($annotationKey, $fusionAst['__prototypes'][$prototypeName]['__meta'])
        ) {
            throw new \Exception(
                sprintf(
                    'Prototype "%s" has no snapshot configuration. ' .
                    'Remember to add one one under "@snapshot" in your fusion code.',
                    $prototypeName
                ),
                1539026681
            );
        }
    }
}
