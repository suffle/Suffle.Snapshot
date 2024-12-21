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
 */

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\ViewInterface;
use Suffle\Snapshot\Traits\PackageTrait;

class OverviewController extends ActionController
{
    use PackageTrait;

    protected array $sitePackage;

    public function initializeView(ViewInterface $view): void
    {
        if (!$this->sitePackage) {
            $this->sitePackage = $this->getFirstOnlineSitePackage();
        }

        $this->view->assign('currentSitePackageKey', $this->sitePackage['packageKey']);
    }

    public function indexAction(): void
    {
    }
}
