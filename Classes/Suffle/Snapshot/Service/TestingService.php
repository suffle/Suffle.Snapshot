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
use SebastianBergmann\Diff\Differ;
use Suffle\Snapshot\Diff\DiffOutputBuilder;
use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Fusion\FusionView;
use Suffle\Snapshot\Traits\OutputTrait;
use Suffle\Snapshot\Traits\PackageTrait;
use Suffle\Snapshot\Traits\SimulateContextTrait;


/**
 * Service to run tests on fusion components
 */
class TestingService
{
    use SimulateContextTrait, OutputTrait, PackageTrait;

    public const UPDATE_SNAPSHOT_ANSWERS = [
        "y" => "update this snapshot",
        "n" => "do not update this snapshot",
        "q" => "do not update and quit immediately",
        "a" => "update this and all following failed snapshots",
        "d" => "do not update this or any following snapshots"
    ];

    #[Flow\Inject]
    protected FusionService $fusionService;

    private int $totalTests = 0;
    private int $failedTests = 0;
    private array $failedPrototypes = [];
    private int $newSnapshots = 0;
    private bool $testSuccess = true;
    private bool $interactiveMode;
    private bool $updateAllSnapshots;
    private bool $skipAllSnapshots = false;
    private ?array $sitePackages;
    private array $detailedTestResults = [];

    /**
     * Constructs the command controller
     */
    public function __construct(string $packageKey = null, bool $interactive = false, bool $updateAll = false)
    {
        $this->sitePackages = $packageKey ? array($this->getSitePackageByKey($packageKey)) : null;
        $this->interactiveMode = $interactive;
        $this->updateAllSnapshots = $updateAll;
    }

    public function testAllPrototypes(): array
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
                $this->testSinglePrototype($prototypeName, $sitePackageKey);
            }
        }

        return $this->getStats();
    }

    public function testPrototype(string $prototypeName): array
    {
        $this->reset();
        $this->sitePackages = $this->sitePackages ?: $this->getSitePackages();

        foreach($this->sitePackages as $sitePackage) {
            $sitePackageKey = $sitePackage['packageKey'];
            $this->injectBaseUriIntoFileSystemTargets($sitePackage['baseUri']);
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $this->testSinglePrototype($prototypeName, $sitePackageKey);
        }


        return $this->getStats();
    }

    private function testSinglePrototype(string $prototypeName, string $sitePackageKey): void
    {
        $prototypePreviewRenderPath = FusionService::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'], $prototypeName);

        $fusionView = new FusionView();
        $fusionView->setControllerContext($this->createDummyContext());
        $fusionView->setFusionPath($prototypePreviewRenderPath);
        $fusionView->setPackageKey($sitePackageKey);

        try {
            $renderedPrototypes = $fusionView->renderSnapshotPrototype($prototypeName);
        } catch (\Throwable $e) {
            $this->outputFailed("Error rendering prototype %s: %s", [$prototypeName, $e->getMessage()], 1);
            return;
        }

        $builder = new DiffOutputBuilder();
        $differ = new Differ($builder);

        $snapshotService = new SnapshotService($sitePackageKey);

        $this->outputInfoText($prototypeName, 1);

        foreach ($renderedPrototypes as $propSetName => $renderedPrototype) {
            try {
                $savedSnapshot = $snapshotService->getSnapshotOfPropSet($prototypeName, $propSetName, $sitePackageKey);
            } catch (FilesException $e) {
                $this->outputFailed("Error reading snapshot for propSet %s: %s", [$propSetName, $e->getMessage()], 2);
                continue;
            }

            if (!$savedSnapshot) {
                $this->outputInfo("No snapshot found for propSet %s", [$propSetName], 2);
                $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                ++$this->newSnapshots;
                $this->detailedTestResults[$prototypeName][$propSetName] = [
                    'success' => false,
                    'newSnapshot' => true
                ];
                continue;
            }

            $diff = $differ->diff($savedSnapshot, $renderedPrototype);

            if ($diff && $this->updateAllSnapshots) {
                $this->outputInfo("Auto-update snapshot for propSet %s", [$propSetName], 2);
                $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                ++$this->newSnapshots;
                $this->detailedTestResults[$prototypeName][$propSetName] = [
                    'success' => false,
                    'newSnapshot' => true
                ];
                continue;
            }

            if (!$diff || $this->skipAllSnapshots || !$this->interactiveMode) {
                $this->makeTest($diff, $propSetName, $prototypeName);
                continue;
            }

            $this->outputInfo("PropSet %s has changed", [$propSetName], 2);
            $this->outputTabbed($diff, [], 3);
            $this->outputNewLine();
            // give non-existing answer as default to force decision
            $answer = $this->waitAndAsk("Update snapshot?", self::UPDATE_SNAPSHOT_ANSWERS, "false", 2);

            switch($answer) {
                case "y":
                    $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                    ++$this->newSnapshots;
                    $this->detailedTestResults[$prototypeName][$propSetName] = [
                        'success' => false,
                        'newSnapshot' => true
                    ];
                    break;
                case "n":
                    $this->makeTest($diff, $propSetName, $prototypeName);
                    break;
                case "q":
                    throw new \RuntimeException('Testing aborted');
                case "a":
                    $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                    ++$this->newSnapshots;
                    $this->updateAllSnapshots = true;
                    $this->detailedTestResults[$prototypeName][$propSetName] = [
                        'success' => false,
                        'newSnapshot' => true
                    ];
                    break;
                case "d":
                    $this->skipAllSnapshots = true;
                    $this->makeTest($diff, $propSetName, $prototypeName);
                    break;
            }
        }

        $this->outputNewLine();
    }

    private function makeTest(string $diff, string $propSetName, string $prototypeName): void
    {
        ++$this->totalTests;

        if (!$diff) {
            $this->outputSuccess($propSetName, [], 2);
        } else {
            $this->testSuccess = false;
            ++$this->failedTests;

            if ($this->failedPrototypes && array_key_exists($prototypeName, $this->failedPrototypes)) {
                $this->failedPrototypes[$prototypeName][] = $propSetName;
            } else {
                $this->failedPrototypes[$prototypeName] = array($propSetName);
            }

            $this->outputFailed($propSetName, [], 2);
            $this->outputNewLine();
            $this->outputTabbed($diff, [], 3);
            $this->outputNewLine();
        }

        $this->detailedTestResults[$prototypeName][$propSetName] = [
            'success' => !$diff,
            'newSnapshot' => false
        ];
    }

    protected function reset(): void
    {
        $this->failedTests = 0;
        $this->totalTests = 0;
        $this->newSnapshots = 0;
        $this->testSuccess = true;
    }

    private function getStats(): array
    {
        return [
            'success' => $this->testSuccess,
            'newSnapshots' => $this->newSnapshots,
            'failedTests' => $this->failedTests,
            'totalTests' => $this->totalTests,
            'detailedResults' => $this->detailedTestResults,
            'failedPrototypes' => $this->failedPrototypes
        ];
    }
}
