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
use Suffle\Snapshot\Traits\PackageTrait;

class OverviewController extends ActionController
{

    use PackageTrait;
    /**
     * @var array
     */
    protected $sitePackage;

    /**
     * @param  ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        if (!$this->sitePackage) {
            $this->sitePackage = $this->getFirstOnlineSitePackage();
        }

        $this->view->assign('currentSitePackageKey', $this->sitePackage['packageKey']);
    }
    /**
     * @return void
     */
    public function indexAction()
    {
    }
}
