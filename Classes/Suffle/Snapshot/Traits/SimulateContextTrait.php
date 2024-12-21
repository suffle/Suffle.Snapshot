<?php

declare(strict_types=1);
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

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\ResourceManagement\Collection;
use Neos\Flow\ResourceManagement\ResourceManager;
use Suffle\Snapshot\Resource\Target\OverridableFileSystemTarget;

/**
 * Utility trait to create controller contexts within CLI SAPI
 */
trait SimulateContextTrait
{
    #[Flow\Inject]
    protected ResourceManager $resourceManager;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * Create a dummy controller context
     */
    protected function createDummyContext(): ControllerContext
    {
        if (!$this->controllerContext) {
            $arguments = new Arguments([]);

            // From Flow 6+ we have to use a static method to create an ActionRequest. Earlier versions use the constructor.
            $actionRequest = ActionRequest::fromHttpRequest(new ServerRequest('GET', new Uri('http://neos.io')));
            $response = new ActionResponse();

            $uriBuilder = new UriBuilder();
            $uriBuilder
                ->setRequest($actionRequest);
            $uriBuilder
                ->setFormat('html')
                ->setCreateAbsoluteUri(false);

            $this->controllerContext = new ControllerContext($actionRequest, $response, $arguments, $uriBuilder);
        }

        return $this->controllerContext;
    }

    /**
     * Override the baseUri of static resource targets
     *
     * This is needed because the rendering can be executed via CLI without a baseUri
     */
    protected function injectBaseUriIntoFileSystemTargets(string $baseUri): void
    {
        // Make sure the base URI ends with a slash
        $baseUri = rtrim($baseUri, '/') . '/';

        $collections = $this->resourceManager->getCollections();

        /** @var Collection $collection */
        foreach ($collections as $collection) {
            $target = $collection->getTarget();
            if ($target instanceof OverridableFileSystemTarget) {
                $target->setCustomBaseUri($baseUri);
            }
        }
    }
}
