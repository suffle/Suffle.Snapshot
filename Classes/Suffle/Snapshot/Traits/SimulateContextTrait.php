<?php
namespace Suffle\Snapshot\Traits;

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

use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Suffle\Snapshot\Resource\Target\OverridableFileSystemTarget;

use Neos\Flow\Annotations as Flow;

/**
 * Utility trait to create controller contexts within CLI SAPI
 */
trait SimulateContextTrait
{
    /**
    * @Flow\Inject
    * @var \Neos\Flow\ResourceManagement\ResourceManager
    */
    protected $resourceManager;

    /**
     * Create a dummy controller context
     *
     * @return ControllerContext
     */
    protected function createDummyContext(): ControllerContext
    {
        $httpRequest = Request::create(new Uri('http://neos.io'));
        $request = new ActionRequest($httpRequest);
        $response = new Response();
        $arguments = new Arguments([]);
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($request);

        return new ControllerContext($request, $response, $arguments, $uriBuilder);
    }

    /**
     * Override the baseUri of static resource targets
     *
     * This is needed because the rendering can be executed via CLI without a baseUri
     *
     * @param string $baseUri
     * @throws \Neos\Utility\Exception\PropertyNotAccessibleException
     */
    protected function injectBaseUriIntoFileSystemTargets($baseUri)
    {
        // Make sure the base URI ends with a slash
        $baseUri = rtrim($baseUri, '/') . '/';

        $collections = $this->resourceManager->getCollections();

        /** @var \Neos\Flow\ResourceManagement\Collection $collection */
        foreach ($collections as $collection) {
            $target = $collection->getTarget();
            if ($target instanceof OverridableFileSystemTarget) {
                $target->setCustomBaseUri($baseUri);
            }
        }
    }
}


