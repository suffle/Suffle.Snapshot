<?php

namespace Suffle\Snapshot\Command;

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

use Suffle\Snapshot\Fusion\FusionService;
use Suffle\Snapshot\Service\TestingService;
use Suffle\Snapshot\Service\SnapshotService;
use Suffle\Snapshot\Traits\PackageTrait;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * Class SnapshotCommandController
 * @package Suffle\Snapshot\Command
 */
class SnapshotCommandController extends CommandController
{
    use PackageTrait;

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;


    /**
     * Take Snapshot of given Fusion Component
     *
     * @param string $prototypeName to take snapshot of
     * @param string $packageKey site-package (defaults to first found)
     * @throws \Exception
     */
    public function takeCommand(string $prototypeName, string $packageKey = null): void
    {
        $snapshotService = new SnapshotService($packageKey);
        $snapshotStats = $snapshotService->takeSnapshotOfPrototype($prototypeName);

        $this->outputSnapshotResults($snapshotStats);
    }

    /**
     * Take Snapshots of all Fusion Components
     *
     * @param string $packageKey site-package (defaults to first found)
     * @throws \Exception
     */
    public function takeAllCommand(string $packageKey = null): void
    {
        $snapshotService = new SnapshotService($packageKey);
        $snapshotStats = $snapshotService->takeSnapshotOfAllPrototypes();

        $this->outputSnapshotResults($snapshotStats);

    }

    /**
     * Test given Fusion Component
     *
     * @param string §prototypeName name of prototype to be tested
     * @param bool $interactive use interactive mode
     * @param bool $updateall update all failed snapshots
     * @param string $packageKey site-package (defaults to first found)
     * @throws \Exception
     */
    public function testCommand(string $prototypeName, bool $interactive = false, bool $updateall = false, string $packageKey = null): void
    {
        $testingService = new TestingService($packageKey, $interactive, $updateall);
        $testStats = $testingService->testPrototype($prototypeName);

        $this->outputTestResults($testStats);
    }

    /**
     * Test all Fusion Components
     *
     * @param bool $interactive use interactive mode
     * @param bool $updateall update all failed snapshots
     * @param string $packageKey site-package (defaults to first found)
     * @throws \Exception
     */
    public function testAllCommand(bool $interactive = false, bool $updateall = false, $packageKey = null): void
    {
        $testingService = new TestingService($packageKey, $interactive, $updateall);
        $testStats = $testingService->testAllPrototypes();

        $this->outputTestResults($testStats);

    }

    /**
     * Get all items currently available for testing
     *
     * @param string $packageKey site-package (defaults to first found)
     * @throws \Exception
     */
    public function itemsCommand(string $packageKey = null): void
    {
        $sitePackageKeys = $packageKey ? [$packageKey] : $this->getActiveSitePackageKeys();

        foreach ($sitePackageKeys as $sitePackageKey) {
            $this->outputLine($sitePackageKey);
            $testablePrototypes = $this->fusionService->getPrototypeNamesForTesting($sitePackageKey);

            foreach ($testablePrototypes as $prototypeName) {
                $this->outputLine("\t" . $prototypeName);
            }
        }

    }

    /**
     * Output results returned from snapshot service
     *
     * @param array $stats
     * @throws \Exception
     */
    private function outputSnapshotResults(array $stats): void
    {
        $successfulSnapshots = $stats['totalSnapshots'] - $stats['failedSnapshots'];

        $this->output(PHP_EOL . $stats['totalSnapshots'] . " Snapshots rendered.</>" . PHP_EOL);

        if ($successfulSnapshots) {
            $this->output("<fg=green>" . $successfulSnapshots . " Snapshots saved.</>" . PHP_EOL);
        }

        if ($stats['failedSnapshots']) {
            $this->output("<fg=red>" . $stats['failedSnapshots'] . " Snapshots could not be saved.</>" . PHP_EOL);
        }


        if (!$stats['success']) {
            throw new \Exception('Not all Snapshots could be written');
        }

        $this->output(PHP_EOL . "<fg=green>All Snapshots saved successfully</>");
    }

    /**
     * Output results returned from TestService
     *
     * @param array $stats
     * @throws \Exception
     */
    private function outputTestResults(array $stats): void
    {
        $successfulTests = $stats['totalTests'] - $stats['failedTests'];

        $this->output(PHP_EOL . $stats['totalTests'] . " Tests run.</>" . PHP_EOL);

        if ($stats['newSnapshots']) {
            $this->output($stats['newSnapshots'] . " new Snapshots written</>" . PHP_EOL);
        }

        if ($successfulTests) {
            $this->output("<fg=green>" . $successfulTests . " Tests successful.</>" . PHP_EOL);
        }

        if ($stats['failedTests']) {
            $this->output("<fg=red>" . $stats['failedTests'] . " Tests failed</>" . PHP_EOL);
        }


        if (!$stats['success']) {
            throw new \Exception('Snapshot Testing failed');
        }

        $this->output(PHP_EOL . "<fg=green>Snapshot testing successful</>");
    }
}
