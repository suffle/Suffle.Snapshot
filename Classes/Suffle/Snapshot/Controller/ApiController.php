<?php
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
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Fusion\View\FusionView as NeosFusionView;
use SebastianBergmann\Diff\Differ;
use Suffle\Snapshot\Traits\SimulateContextTrait;
use Suffle\Snapshot\Traits\PackageTrait;
use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Fusion\FusionView;
use Suffle\Snapshot\Service\TestingService;
use Suffle\Snapshot\Service\SnapshotService;
use Suffle\Snapshot\Diff\DiffOutputBuilder;

class ApiController extends ActionController {
    use PackageTrait, SimulateContextTrait;

    /**
     * @var array
     */
    protected $defaultViewObjectName = 'Neos\Flow\Mvc\View\JsonView';

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;

    public function __construct()
    {
        $this->controllerContext = $this->createDummyContext();
    }

    /**
     * get all names of testable objects
     *
     * @Flow\SkipCsrfProtection
     * @param string $packageKey
     * @return void
     */
    public function snapshotObjectsAction($packageKey = null)
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
     *
     * @Flow\SkipCsrfProtection
     * @param string $prototypeName
     * @param string $packageKey
     * @return void
     */
    public function snapshotDataAction($prototypeName, $packageKey = null)
    {
        $packageKey = $packageKey ?: $this->getFirstOnlineSitePackageKey();
        $prototypePreviewRenderPath = FusionService::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'], $prototypeName);

        $fusionView = new FusionView();
        $fusionView->setControllerContext($this->controllerContext);
        $fusionView->setFusionPath($prototypePreviewRenderPath);
        $fusionView->setPackageKey($packageKey);

        $snapshotService = new SnapshotService($packageKey);

        $result = [];

        $builder = new DiffOutputBuilder();
        $differ = new Differ($builder);

        $renderedPrototypes = $fusionView->renderSnapshotPrototype($prototypeName);

        foreach ($renderedPrototypes as $propSetName => $renderedPrototype) {
            $savedSnapshot = $snapshotService->getSnapshotOfPropSet($prototypeName, $propSetName, $packageKey);

            if ($savedSnapshot) {
                $diff = $differ->diff($savedSnapshot, $renderedPrototype);
            }

            $result[$propSetName] = [
                'snapshot' => $savedSnapshot,
                'current' => $renderedPrototype,
                'hasSnapshot' => $savedSnapshot ? true : false,
                'testSuccess' => $diff ? false : true
            ];
        }

        $this->view->assign('value', $result);
    }

    /**
     * Get preview markup
     *
     * @Flow\SkipCsrfProtection
     * @param string $packageKey
     * @return void
     */
    public function previewMarkupAction($packageKey = null)
    {
        $packageKey = $packageKey ?: $this->getFirstOnlineSitePackageKey();
        $previewPrototypeName = $this->settings['previewPrototypeName'];
        $prototypePreviewRenderPath = str_replace(['.', ':'], ['_', '__'], $previewPrototypeName);

        $fusionView = new FusionView();

        $fusionRootPath = sprintf('/<%s>', $prototypePreviewRenderPath);
        $fusionView->setControllerContext($this->controllerContext);
        $fusionView->setPackageKey($packageKey);

        // get the status and headers from the view
        $result = [
            'previewMarkup' => $fusionView->renderPrototype($previewPrototypeName)
        ];


        $this->view->assign('value', $result);
    }

    /**
     * Get all site packages
     *
     * @Flow\SkipCsrfProtection
     * @return void
     */
    public function sitePackagesAction()
    {
        $sitePackages = $this->getSitePackages();
        $result = [];

        foreach ($sitePackages as $sitePackage) {
            $result[] = $sitePackage['packageKey'];
        }

        $this->view->assign('value', $result);
    }
}
