<?php

declare(strict_types=1);
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

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Exception\FilesException;
use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Fusion\FusionView;
use Suffle\Snapshot\Traits\OutputTrait;
use Suffle\Snapshot\Traits\PackageTrait;
use Suffle\Snapshot\Traits\SimulateContextTrait;

/**
 * Service to load and save snapshot files
 */
#[Flow\Scope("singleton")]
class SnapshotService
{
    use SimulateContextTrait, OutputTrait, PackageTrait;

    #[Flow\Inject]
    protected FusionService $fusionService;

    #[Flow\Inject]
    protected FileStorage $fileStorage;

    protected int $totalSnapshots = 0;
    protected int $failedSnapshots = 0;
    protected bool $snapshotsSuccessful = true;
    protected ?array $sitePackages = null;

    /**
     * Constructs the command controller
     */
    public function __construct(string $packageKey = null)
    {
        $this->sitePackages = $packageKey ? $this->getSitePackageByKey($packageKey) : null;
    }

    public function takeSnapshotOfAllPrototypes(): array
    {
        $this->reset();
        $this->sitePackages = $this->sitePackages ?: $this->getSitePackages();

        foreach ($this->sitePackages as $sitePackage) {
            $sitePackageKey = $sitePackage['packageKey'];
            $this->injectBaseUriIntoFileSystemTargets($sitePackage['baseUri']);
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $prototypesToSnapshot = $this->fusionService->getPrototypeNamesForTesting($sitePackageKey);

            foreach ($prototypesToSnapshot as $prototypeName) {
                $this->takeSinglePrototype($prototypeName, $sitePackageKey);
            }
        }

        return $this->getStats();
    }

    public function takeSnapshotOfPrototype(string $prototypeName): array
    {
        $this->reset();
        $this->sitePackages = $this->sitePackages ?: $this->getSitePackages();

        foreach ($this->sitePackages as $sitePackage) {
            $sitePackageKey = $sitePackage['packageKey'];
            $this->injectBaseUriIntoFileSystemTargets($sitePackage['baseUri']);
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $this->takeSinglePrototype($prototypeName, $sitePackageKey);
        }

        return $this->getStats();
    }

    public function takeSnapshotOfPropSet(
        string $html,
        string $prototypeName,
        string $propSetName,
        string $sitePackageName
    ): array {
        $this->reset();
        $this->takeSinglePropSet($html, $prototypeName, $propSetName, $sitePackageName);

        return $this->getStats();
    }

    /**
     * @throws FilesException
     */
    public function getSnapshotOfPropSet(string $prototypeName, string $propSetName, string $sitePackageKey): string
    {
        return $this->fileStorage->getSnapshotByPropSet($prototypeName, $propSetName, $sitePackageKey);
    }

    private function takeSinglePropSet(
        string $html,
        string $prototypeName,
        string $propSetName,
        string $sitePackageKey
    ): bool {
        if (!$html) {
            $this->outputInfoText("Snapshot for PropSet " . $propSetName . " did not return any Markup. Skipped." . PHP_EOL,
                2);
            return true;
        }

        try {
            if ($this->fileStorage->saveSnapshotByPropSet($html, $prototypeName, $propSetName, $sitePackageKey)) {
                $this->outputSuccess("Snapshot written for PropSet " . $propSetName . PHP_EOL, [], 2);
                return true;
            }
        } catch (FilesException $e) {
            $this->outputFailed("Snapshot could not be written for PropSet " . $propSetName . ': ' . $e->getMessage() . PHP_EOL,
                [], 2);
            return false;
        }
        $this->outputFailed("Snapshot could not be written for PropSet " . $propSetName . PHP_EOL, [], 2);
        return false;
    }

    /**
     * Take snapshot of prototype including all propSets
     */
    private function takeSinglePrototype(string $prototypeName, string $sitePackageKey): void
    {
        $prototypePreviewRenderPath = FusionService::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'],
                $prototypeName);

        $fusionView = new FusionView();

        $fusionView->setControllerContext($this->createDummyContext());
        $fusionView->setFusionPath($prototypePreviewRenderPath);
        $fusionView->setPackageKey($sitePackageKey);

        try {
            $renderedPrototypes = $fusionView->renderSnapshotPrototype($prototypeName);
        } catch (\Throwable $e) {
            $this->outputFailed("Could not render Prototype " . $prototypeName . ': ' . $e->getMessage() . PHP_EOL);
            return;
        }
        $this->outputInfoText($prototypeName, 1);

        foreach ($renderedPrototypes as $propSetName => $html) {
            ++$this->totalSnapshots;

            if (!$this->takeSinglePropSet($html, $prototypeName, $propSetName, $sitePackageKey)) {
                ++$this->failedSnapshots;
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
