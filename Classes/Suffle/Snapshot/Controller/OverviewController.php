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
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * Class ModuleController
 * @package Suffle\Monocle\Controller
 */
class DiffController extends ActionController
{
    /**
     * @return void
     */
    public function indexAction(string $sitePackageKey, string $prototypeName)
    {
        $testingService = new TestingService($sitePackageKey);
        $testResults = $testingService->testPrototype($prototypeName, true);
        $testResults = array_key_exists($prototypeName, $testResults['detailedResults']) ? $testResults['detailedResults'][$prototypeName] : null;


        $this->view->assignMultiple([
            'testResults' => $testResults,
            'prototypeName' => $prototypeName
        ]);
    }
}
