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

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Exception\InvalidActionNameException;
use Neos\Flow\Mvc\Exception\InvalidArgumentNameException;
use Neos\Flow\Mvc\Exception\InvalidArgumentTypeException;
use Neos\Flow\Mvc\Exception\InvalidControllerNameException;
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
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * Create a dummy controller context
     *
     * @return ControllerContext
     * @throws InvalidActionNameException
     * @throws InvalidArgumentNameException
     * @throws InvalidArgumentTypeException
     * @throws InvalidControllerNameException
     */
    protected function createDummyContext(): ControllerContext
    {
        if (!$this->controllerContext) {
            $arguments = new Arguments([]);

            if (method_exists(ActionRequest::class, 'fromHttpRequest')) {
                // From Flow 6+ we have to use a static method to create an ActionRequest. Earlier versions use the constructor.
                $actionRequest = ActionRequest::fromHttpRequest(new ServerRequest('GET', new Uri('http://neos.io')));
                $response = new ActionResponse();
            } else {
                // This can be cleaned up when this package in a future release only support Flow 6+.
                $httpRequest = \Neos\Flow\Http\Request::create(new \Neos\Flow\Http\Uri('http://neos.io'));
                $actionRequest = new ActionRequest($httpRequest);
                $response = new \Neos\Flow\Http\Response();
            }

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


