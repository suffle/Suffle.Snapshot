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

use Suffle\Snapshot\Traits\OutputTrait;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;


/**
 * Service to store and read files
 */
class FileStorage
{
    use OutputTrait;
    const MAX_FILENAME_LENGTH = 255;

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;


    /**
     * Stores snapshots of given prototype and propSet.
     *
     * @param string $html Html of the prototype and propSet that should be saved
     * @param string $prototypeName The name of the prototype that should be saved
     * @param string $propSetName rendered prototypes from propSets
     * @param string $sitePackageName Name of sitePackage to take snapshot from
     * @return bool
     * @throws FilesException
     */
    public function saveSnapshotByPropSet(string $html, string $prototypeName, string $propSetName, string $sitePackageName): bool
    {
        return $this->createSnapshotFile($html, $prototypeName, $propSetName, $sitePackageName);
    }

    /**
     * Get saved snapshot of given prototype and its propSetName.
     *
     * @param string $prototypeName
     * @param string $propSetName
     * @param string $sitePackageName
     * @return string
     * @throws FilesException
     */
    public function getSnapshotByPropSet(string $prototypeName, string $propSetName, string $sitePackageName): string
    {
        return $this->loadSnapshotByPropSetName($prototypeName, $propSetName, $sitePackageName);
    }

    /**
     * @param string $html
     * @param string $prototypeName
     * @param string $propSetName
     * @param string $sitePackageName
     * @return bool
     * @throws FilesException
     */

    private function createSnapshotFile(string $html, string $prototypeName, string $propSetName, string $sitePackageName): bool
    {
        $filePath = $this->getSavePathForSnapshot([$sitePackageName, $prototypeName, $propSetName]);
        file_put_contents($filePath, $html);
        if (!file_exists($filePath)) {
            return false;
        }


        return true;
    }

    /**
     * @param string $prototypeName
     * @param string $propSetName
     * @param string $sitePackageName
     * @return string
     * @throws FilesException
     */
    private function loadSnapshotByPropSetName(string $prototypeName, string $propSetName, string $sitePackageName): string
    {
        $filePath = $this->getSavePathForSnapshot([$sitePackageName, $prototypeName, $propSetName]);

        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }

        return '';
    }

    /**
     * Generate file name and folder
     *
     * @param array $fileNameComponents
     * @return string
     * @throws FilesException
     */
    private function getSavePathForSnapshot(array $fileNameComponents): string
    {
        $fileName = array_reduce($fileNameComponents, function ($acc, $item) {
            if ($acc) {
                $acc .= "_";
            }

            $acc .= str_replace(['.', ':', ' ', '-'], '_', $item);

            return $acc;
        });

        $fileName .= ".snap";

        $savePath = Files::concatenatePaths([$this->settings['snapshotSavePath'], $fileName]);
        $directoryPath = dirname($savePath);

        if (strlen($fileName) > self::MAX_FILENAME_LENGTH) {
            $shortenedFilename = $this->truncateString($fileName, self::MAX_FILENAME_LENGTH - strlen($directoryPath));
            $savePath = Files::concatenatePaths([$this->settings['snapshotSavePath'], $directoryPath, $shortenedFilename . '.snap']);
        }

        Files::createDirectoryRecursively(dirname($savePath));

        return $savePath;
    }

    /**
     * Truncate an identifier if needed and append a hash to ensure uniqueness.
     *
     * @param string $string
     * @param integer $lengthLimit
     * @return string
     */
    private function truncateString(string $string, int $lengthLimit): string
    {
        if (strlen($string) > $lengthLimit) {
            $string = substr($string, 0, $lengthLimit - 6) . '_' . substr(sha1($string), 0, 5);
        }

        return $string;
    }
}
