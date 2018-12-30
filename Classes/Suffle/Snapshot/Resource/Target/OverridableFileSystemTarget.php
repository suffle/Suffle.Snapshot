<?php
namespace Suffle\Snapshot\Resource\Target;

use Neos\Flow\ResourceManagement\Target\FileSystemTarget;

class OverridableFileSystemTarget extends FileSystemTarget {

    /**
     * @var string
     */
    protected $customBaseUri;

    /**
     * @return string
     */
    protected function getResourcesBaseUri()
    {
        if ($this->customBaseUri === null) {
            return parent::getResourcesBaseUri();
        }

        return $this->customBaseUri . $this->baseUri;
    }

    /**
     * @param string $baseUri
     */
    public function setCustomBaseUri(string $baseUri)
    {
        $this->customBaseUri = $baseUri;
    }

}