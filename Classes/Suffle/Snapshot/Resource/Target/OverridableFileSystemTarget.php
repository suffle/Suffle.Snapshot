<?php

declare(strict_types=1);
namespace Suffle\Snapshot\Resource\Target;

use Neos\Flow\ResourceManagement\Target\FileSystemTarget;

class OverridableFileSystemTarget extends FileSystemTarget {

    protected ?string $customBaseUri = null;

    protected function getResourcesBaseUri(): string
    {
        if ($this->customBaseUri === null) {
            return parent::getResourcesBaseUri();
        }

        return $this->customBaseUri . $this->baseUri;
    }

    public function setCustomBaseUri(string $baseUri): void
    {
        $this->customBaseUri = $baseUri;
    }

}
