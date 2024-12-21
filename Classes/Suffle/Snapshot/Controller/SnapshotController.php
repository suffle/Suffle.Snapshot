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

use Neos\ContentRepository\TypeConverter\NodeConverter;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Suffle\Snapshot\Traits\PackageTrait;

class SnapshotController extends AbstractModuleController
{
    use PackageTrait;

    protected ?array $sitePackage = null;

    /**
     * @throws NoSuchArgumentException
     */
    protected function initializeAction(): void
    {
        if ($this->arguments->hasArgument('node')) {
            $this->arguments->getArgument('node')->getPropertyMappingConfiguration()->setTypeConverterOption(
                NodeConverter::class,
                NodeConverter::REMOVED_CONTENT_SHOWN, true
            );

        }

        if (!$this->sitePackage) {
            $this->sitePackage = $this->getFirstOnlineSitePackage();
        }

        parent::initializeAction();
    }

    public function indexAction(): void
    {

    }
}
