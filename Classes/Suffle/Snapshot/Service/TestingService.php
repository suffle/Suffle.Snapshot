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
use Suffle\Snapshot\Diff\DiffOutputBuilder;
use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Fusion\FusionView;
use Suffle\Snapshot\Traits\DummyContextTrait;
use Suffle\Snapshot\Traits\OutputTrait;
use Suffle\Snapshot\Traits\PackageTrait;

use Neos\Flow\Annotations as Flow;

use SebastianBergmann\Diff\Differ;


/**
 * Service to run tests on fusion components
 */
class TestingService
{
    use DummyContextTrait, OutputTrait, PackageTrait;

    const UPDATE_SNAPSHOT_ANSWERS = array(
        "y" => "update this snapshot",
        "n" => "do not update this snapshot",
        "q" => "do not update and quit immediately",
        "a" => "update this and all following failed snapshots",
        "d" => "do not update this or any following snapshots"
    );

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * @var int >= 0
     */
    private $totalTests = 0;

    /**
     * @var int >= 0
     */
    private $failedTests = 0;

    /**
     * @var int >= 0
     */
    private $newSnapshots = 0;

    /**
     * @var bool
     */
    private $testSuccess = true;

    /**
     * @var bool
     */
    private $interactiveMode = false;

    /**
     * @var bool
     */
    private $updateAllSnapshots = false;

    /**
     * @var bool
     */
    private $skipAllSnapshots = false;

    /**
     * @var array
     */
    private $sitePackageKeys;

    /**
     * @var ControllerContext
     */
    private $controllerContext;

    /**
     * Constructs the command controller
     * @param string $packageKey
     * @param bool $interactive
     * @param $updateAll
     */
    public function __construct(string $packageKey = null, $interactive = false, $updateAll)
    {
        $this->sitePackageKeys = $packageKey ? [$packageKey] : null;
        $this->controllerContext = $this->createDummyContext();
        $this->interactiveMode = $interactive;
        $this->updateAllSnapshots = $updateAll;
    }

    /**
     * @param string $prototypeName
     * @return array
     * @throws \Exception
     * @throws \Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException
     * @throws \Neos\Flow\Mvc\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function testPrototype(string $prototypeName): array
    {
        $this->reset();
        $this->sitePackageKeys = $this->sitePackageKeys ?: $this->getActiveSitePackageKeys();

        foreach($this->sitePackageKeys as $sitePackageKey) {
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $this->testSinglePrototype($prototypeName, $sitePackageKey);
        }


        return $this->getStats();
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
    public function testAllPrototypes(): array
    {
        $this->reset();
        $this->sitePackageKeys = $this->sitePackageKeys ?: $this->getActiveSitePackageKeys();

        foreach($this->sitePackageKeys as $sitePackageKey) {
            $this->outputInfoText($sitePackageKey);
            $this->outputNewLine();
            $prototypesToSnapshot = $this->fusionService->getPrototypeNamesForTesting($sitePackageKey);

            foreach($prototypesToSnapshot as $prototypeName) {
                $this->testSinglePrototype($prototypeName, $sitePackageKey);
            }
        }

        return $this->getStats();
    }

    /**
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
    private function testSinglePrototype(string $prototypeName, string $sitePackageKey): void
    {
        $prototypePreviewRenderPath = FusionService::RENDERPATH_DISCRIMINATOR . str_replace(['.', ':'], ['_', '__'], $prototypeName);

        $fusionView = new FusionView();
        $fusionView->setControllerContext($this->controllerContext);
        $fusionView->setFusionPath($prototypePreviewRenderPath);
        $fusionView->setPackageKey($sitePackageKey);

        $renderedPrototypes = $fusionView->renderSnapshotPrototype($prototypeName);

        $builder = new DiffOutputBuilder();
        $differ = new Differ($builder);

        $snapshotService = new SnapshotService($sitePackageKey);

        $this->outputInfoText($prototypeName, 1);

        foreach ($renderedPrototypes as $propSetName => $renderedPrototype) {
            $savedSnapshot = $snapshotService->getSnapshotOfPropSet($prototypeName, $propSetName, $sitePackageKey);

            if (!$savedSnapshot) {
                $this->outputInfo("No snapshot found for propSet %s", [$propSetName], 2);
                $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                $this->newSnapshots += 1;
                continue;
            }

            $diff = $differ->diff($savedSnapshot, $renderedPrototype);

            if ($diff && $this->updateAllSnapshots) {
                $this->outputInfo("Auto-update snapshot for propSet %s", [$propSetName], 2);
                $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                $this->newSnapshots += 1;
                continue;
            }

            if (!$diff || $this->skipAllSnapshots || !$this->interactiveMode) {
                $this->makeTest($diff, $propSetName);
                continue;
            }

            if ($diff && $this->interactiveMode) {
                $this->outputInfo("PropSet %s has changed", [$propSetName], 2);
                $this->outputTabbed($diff, [], 3);
                $this->outputNewLine();
                // give non-existing answer as default to force decision
                $answer = $this->waitAndAsk("Update snapshot?", Self::UPDATE_SNAPSHOT_ANSWERS, "false", 2);

                switch($answer) {
                    case "y":
                        $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                        $this->newSnapshots += 1;
                        break;
                    case "n":
                        $this->makeTest($diff, $propSetName);
                        break;
                    case "q":
                        throw new \Exception('Testing aborted');
                        break;
                    case "a":
                        $snapshotService->takeSnapshotOfPropSet($renderedPrototype, $prototypeName, $propSetName, $sitePackageKey);
                        $this->newSnapshots += 1;
                        $this->updateAllSnapshots = true;
                        break;
                    case "d":
                        $this->skipAllSnapshots = true;
                        $this->makeTest($diff, $propSetName);
                        break;
                }
            }
        }

        $this->outputNewLine();
    }

    private function makeTest($diff, $propSetName)
    {
        $this->totalTests += 1;

        if (!$diff) {
            $this->outputSuccess($propSetName, [], 2);
        } else {
            $this->testSuccess = false;
            $this->failedTests += 1;
            $this->outputFailed($propSetName, [], 2);
            $this->outputNewLine();
            $this->outputTabbed($diff, [], 3);
            $this->outputNewLine();
        }
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
            'totalTests' => $this->totalTests
        ];
    }
}
