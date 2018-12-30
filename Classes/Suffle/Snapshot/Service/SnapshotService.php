<?php
namespace Suffle\Snapshot\Service;

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

use Neos\Flow\Mvc\Controller\ControllerContext;
use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Fusion\FusionView;
use Suffle\Snapshot\Traits\SimulateContextTrait;
use Suffle\Snapshot\Traits\OutputTrait;
use Suffle\Snapshot\Traits\PackageTrait;

use Neos\Flow\Annotations as Flow;



/**
 * Service to load and save snapshot files
 */
class SnapshotService
{
    use SimulateContextTrait, OutputTrait, PackageTrait;

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * @Flow\Inject
     * @var FileStorage
     */
    protected $fileStorage;

    /**
     * @var int >= 0
     */
    private $totalSnapshots = 0;

    /**
     * @var int >= 0
     */
    private $failedSnapshots = 0;

    /**
     * @var bool
     */
    private $snapshotsSuccessful = true;

    /**
     * @var array
     */
    private $sitePackages;

    /**
     * @var ControllerContext
     */
    private $controllerContext;

    /**
     * Constructs the command controller
     * @param string $packageKey
     */
    public function __construct(string $packageKey = null)
    {
        $this->sitePackages = $packageKey ? $this->getSitePackageByKey($packageKey) : null;
        $this->controllerContext = $this->createDummyContext();
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException
     * @throws \Neos\Flow\Mvc\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function takeSnapshotOfAllPrototypes(): array
    {
        $this->reset();
        $this->sitePackages = $this->sitePackages ?: $this->getSitePackages();

        foreach($this->sitePackages as $sitePackage) {
            $sitePackageKey = $sitePackage['packageKey'];
            $this->injectBaseUriIntoFileSystemTargets($sitePackage['baseUri']);
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $prototypesToSnapshot = $this->fusionService->getPrototypeNamesForTesting($sitePackageKey);

            foreach($prototypesToSnapshot as $prototypeName) {
                $this->takeSinglePrototype($prototypeName, $sitePackageKey);
            }
        }

        return $this->getStats();
    }

    /**
     * @param $prototypeName
     * @return array
     * @throws \Exception
     * @throws \Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException
     * @throws \Neos\Flow\Mvc\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function takeSnapshotOfPrototype($prototypeName): array
    {
        $this->reset();
        $this->sitePackages = $this->sitePackages ?: $this->getSitePackages();

        foreach($this->sitePackages as $sitePackage) {
            $sitePackageKey = $sitePackage['packageKey'];
            $this->injectBaseUriIntoFileSystemTargets($sitePackage['baseUri']);
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $this->takeSinglePrototype($prototypeName, $sitePackageKey);
        }

        return $this->getStats();
    }

    /**
     * @param string $html
     * @param string $prototypeName
     * @param string $propSetName
     * @param string $sitePackageName
     * @return array
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function takeSnapshotOfPropSet($html, string $prototypeName, string $propSetName, string $sitePackageName): array
    {
        $this->reset();
        $this->takeSinglePropSet($html, $prototypeName, $propSetName, $sitePackageName);

        return $this->getStats();
    }

    /**
     * @param string $prototypeName
     * @param string $propSetName
     * @param string $sitePackageKey
     * @return string
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function getSnapshotOfPropSet(string $prototypeName, string $propSetName, string $sitePackageKey): string
    {
        return $this->fileStorage->getSnapshotByPropSet($prototypeName, $propSetName, $sitePackageKey);
    }

    /**
     * @param string $html
     * @param string $prototypeName
     * @param string $propSetName
     * @param string $sitePackageKey
     * @return bool
     * @throws \Neos\Utility\Exception\FilesException
     */
    private function takeSinglePropSet($html, string $prototypeName, string $propSetName, string $sitePackageKey): bool
    {
        if (!$html) {
            $this->outputInfoText("Snapshot for PropSet " . $propSetName . " did not return any Markup. Skipped." . PHP_EOL, 2);
            return true;
        }

        if ($this->fileStorage->saveSnapshotByPropSet($html, $prototypeName, $propSetName, $sitePackageKey)) {
            $this->outputSuccess("Snapshot written for PropSet " . $propSetName . PHP_EOL, [], 2);
            return true;
        } else {
            $this->outputFailed("Snapshot could not be written for PropSet " . $propSetName . PHP_EOL, [], 2);
            return false;
        }
    }

    /**
     * Take snapshot of prototype including all propSets
     *
     * @param string $prototypeName
     * @param string $sitePackageKey
     * @throws \Exception
     * @throws \Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException
     * @throws \Neos\Flow\Mvc\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    private function takeSinglePrototype(string $prototypeName, string $sitePackageKey): void
    {
        $prototypePreviewRenderPath = FusionService::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'], $prototypeName);

        $fusionView = new FusionView();

        $fusionView->setControllerContext($this->controllerContext);
        $fusionView->setFusionPath($prototypePreviewRenderPath);
        $fusionView->setPackageKey($sitePackageKey);

        $renderedPrototypes = $fusionView->renderSnapshotPrototype($prototypeName);
        $this->outputInfoText($prototypeName, 1);

        foreach ($renderedPrototypes as $propSetName => $html) {
            $this->totalSnapshots += 1;

            if (!$this->takeSinglePropSet($html, $prototypeName, $propSetName, $sitePackageKey)) {
                $this->failedSnapshots +=1;
                $this->snapshotsSuccessful = false;
            }
        }

        $this->outputNewLine();
    }

    protected function reset(): void
    {
        $this->failedSnapshots = 0;
        $this->totalSnapshots = 0;
        $this->snapshotsSuccessful = true;
    }

    private function getStats(): array
    {
        return [
            'success' => $this->snapshotsSuccessful,
            'failedSnapshots' => $this->failedSnapshots,
            'totalSnapshots' => $this->totalSnapshots
        ];
    }

}
