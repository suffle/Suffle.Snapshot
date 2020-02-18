<?php
namespace Suffle\Snapshot;

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

use Neos\Cache\Backend\BackendInterface;
use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Monitor\FileMonitor;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Package\PackageManager;

/**
 * The Fluid Package
 *
 */
class Package extends BasePackage
{

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $context = $bootstrap->getContext();
        if (!$context->isProduction()) {
            $dispatcher->connect(Sequence::class, 'afterInvokeStep', function ($step) use ($bootstrap, $dispatcher) {
                if ($step->getIdentifier() === 'neos.flow:systemfilemonitor') {
                    $templateFileMonitor = FileMonitor::createFileMonitorAtBoot('Suffle_Snapshot_Fusion_Files', $bootstrap);
                    /**
                     * @var PackageManager $packageManager
                     */
                    $packageManager = $bootstrap->getEarlyInstance(PackageManager::class);

                    foreach ($packageManager->getAvailablePackages() as $packageKey => $package) {
                        if (method_exists($package, 'getResourcesPath')) {
                            $templatesPath = $package->getResourcesPath() . 'Private/Fusion';
                            if (is_dir($templatesPath)) {
                                $templateFileMonitor->monitorDirectory($templatesPath);
                            }
                        }
                    }

                    $templateFileMonitor->detectChanges();
                    $templateFileMonitor->shutdownObject();
                }
            });
        }

        $flushTemplates = function ($identifier, $changedFiles) use ($bootstrap) {
            if ($identifier !== 'Suffle_Snapshot_Fusion_Files') {
                return;
            }

            if ($changedFiles === []) {
                return;
            }

            /** @var BackendInterface $templateCache */
            $templateCache = $bootstrap->getObjectManager()->get(CacheManager::class)->getCache('Suffle_Snapshot_Fusion_Cache');
            $templateCache->flush();
        };
        $dispatcher->connect(FileMonitor::class, 'filesHaveChanged', $flushTemplates);
    }
}
