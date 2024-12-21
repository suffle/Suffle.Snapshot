<?php

declare(strict_types=1);

namespace Suffle\Snapshot\Controller;

/**
 * This file is part of the Suffle.Snapshot package
 *
 * (c) 2018
 * sebastian Flor <sebastian@flor.rocks>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 *
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Utility\Exception\FilesException;
use SebastianBergmann\Diff\Differ;
use Suffle\Snapshot\Diff\DiffOutputBuilder;
use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Fusion\FusionView;
use Suffle\Snapshot\Service\SnapshotService;
use Suffle\Snapshot\Traits\PackageTrait;
use Suffle\Snapshot\Traits\SimulateContextTrait;

class ApiController extends ActionController
{
    use PackageTrait, SimulateContextTrait;

    protected $defaultViewObjectName = JsonView::class;

    #[Flow\Inject]
    protected FusionService $fusionService;

    /**
     * get all names of testable objects
     */
    #[Flow\SkipCsrfProtection]
    public function snapshotObjectsAction(string $packageKey = null): void
    {
        $packageKey = $packageKey ?: $this->getFirstOnlineSitePackageKey();

        $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($packageKey);
        $snapshotObjects = $this->fusionService->getSnapshotObjectsFromFusionAst($fusionAst);
        $objectNames = array_keys($snapshotObjects);
        sort($objectNames);

        $this->view->assign('value', $objectNames);
    }

    /**
     * Get data for single prototype
     */
    #[Flow\SkipCsrfProtection]
    public function snapshotDataAction(string $prototypeName, string $packageKey = null): void
    {
        $packageKey = $packageKey ?: $this->getFirstOnlineSitePackageKey();
        $prototypePreviewRenderPath = FusionService::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'],
                $prototypeName);

        $fusionView = new FusionView();
        $fusionView->setControllerContext($this->createDummyContext());
        $fusionView->setFusionPath($prototypePreviewRenderPath);
        $fusionView->setPackageKey($packageKey);

        $snapshotService = new SnapshotService($packageKey);

        $result = [];

        $builder = new DiffOutputBuilder();
        $differ = new Differ($builder);

        try {
            $renderedPrototypes = $fusionView->renderSnapshotPrototype($prototypeName);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $renderedPrototypes = [];
        }

        foreach ($renderedPrototypes as $propSetName => $renderedPrototype) {
            try {
                $savedSnapshot = $snapshotService->getSnapshotOfPropSet($prototypeName, $propSetName, $packageKey);
            } catch (FilesException $e) {
                $this->logger->error($e->getMessage());
                $savedSnapshot = null;
            }

            if ($savedSnapshot) {
                $diff = $differ->diff($savedSnapshot, $renderedPrototype);
            }

            $result[$propSetName] = [
                'snapshot' => $savedSnapshot,
                'current' => $renderedPrototype,
                'hasSnapshot' => (bool)$savedSnapshot,
                'testSuccess' => $savedSnapshot && !$diff,
            ];
        }

        $this->view->assign('value', $result);
    }

    /**
     * Get preview markup
     */
    #[Flow\SkipCsrfProtection]
    public function previewMarkupAction(string $packageKey = null): void
    {
        $packageKey = $packageKey ?: $this->getFirstOnlineSitePackageKey();
        $previewPrototypeName = $this->settings['previewPrototypeName'];

        $fusionView = new FusionView();
        $fusionView->setControllerContext($this->controllerContext);
        $fusionView->setPackageKey($packageKey);

        // get the status and headers from the view
        try {
            $result = [
                'previewMarkup' => $fusionView->renderPrototype($previewPrototypeName)
            ];
        } catch (\Throwable $e) {
          $result = 'Error: ' . $e->getMessage();
        }
        $this->view->assign('value', $result);
    }

    /**
     * Get all site packages
     */
    #[Flow\SkipCsrfProtection]
    public function sitePackagesAction(): void
    {
        $sitePackages = $this->getSitePackages();
        $result = [];

        foreach ($sitePackages as $sitePackage) {
            $result[] = $sitePackage['packageKey'];
        }

        $this->view->assign('value', $result);
    }
}
