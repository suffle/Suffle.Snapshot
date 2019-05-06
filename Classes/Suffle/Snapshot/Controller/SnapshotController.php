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
 */

use Suffle\Snapshot\Service\TestingService;

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Suffle\Snapshot\Traits\PackageTrait;

/**
 * Class StyleguideController
 * @package Suffle\Snapshot\Controller
 */
class SnapshotController extends AbstractModuleController
{
    use PackageTrait;
    /**
     * @var array
     */
    protected $sitePackage;

    /**
     * @return void
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     */
    protected function initializeAction()
    {
        if ($this->arguments->hasArgument('node')) {
            $this->arguments->getArgument('node')->getPropertyMappingConfiguration()->setTypeConverterOption('Neos\ContentRepository\TypeConverter\NodeConverter',
                \Neos\Neos\TypeConverter\NodeConverter::REMOVED_CONTENT_SHOWN, true);

        }

        if (!$this->sitePackage) {
            $this->sitePackage = $this->getFirstOnlineSitePackage();
        }

        parent::initializeAction();
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $sitePackage = $this->sitePackage;
        $sitePackageKey = $sitePackage['packageKey'];
        $testingService = new TestingService($sitePackageKey, false, false);
        $testResults = $testingService->testAllPrototypes();

        $this->view->assignMultiple([
            'activeSite' => $sitePackageKey,
            'testResults' => $testResults,
            'success' => $testResults['success'] ? 'True' : 'False'
        ]);

    }
}
