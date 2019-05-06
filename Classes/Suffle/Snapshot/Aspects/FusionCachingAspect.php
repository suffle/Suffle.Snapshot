<?php
namespace Suffle\Snapshot\Aspects;

/**
 * This file is part of the Suffle.Snapshot package
 *
 * (c) 2018
 * sebastian Flor <sebastian@flor.rocks>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 *
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Cache\Frontend\VariableFrontend;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class FusionCachingAspect
{
    /**
     * @Flow\Inject
     * @var VariableFrontend
     */
    protected $fusionCache;

    /**
     * @Flow\Around("method(Suffle\Snapshot\Fusion\FusionService->getMergedFusionObjectTreeForSitePackage())")
     * @param JoinPointInterface $joinPoint The current join point
     * @return mixed
     */
    public function cacheGetMergedFusionObjectTree(JoinPointInterface $joinPoint)
    {
        $siteResourcesPackageKey = $joinPoint->getMethodArgument('siteResourcesPackageKey');
        $cacheIdentifier = str_replace('.', '_', $siteResourcesPackageKey);

        if ($this->fusionCache->has($cacheIdentifier)) {
            $fusionObjectTree = $this->fusionCache->get($cacheIdentifier);
        } else {
            $fusionObjectTree = $joinPoint->getAdviceChain()->proceed($joinPoint);
            $this->fusionCache->set($cacheIdentifier, $fusionObjectTree);
        }

        return $fusionObjectTree;
    }
}
