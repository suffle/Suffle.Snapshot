<?php

namespace Suffle\Snapshot\Diff;

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

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;


final class DiffOutputBuilder implements DiffOutputBuilderInterface
{
    /**
     * @var int >= 0
     */
    private $commonLineThreshold = 6;

    /**
     * @var int >= 0
     */
    private $contextLines = 3;

    /**
     * @var string
     */
    private $header;

    public function __construct()
    {
        $this->header = "<fg=white>diff" . PHP_EOL . "--- Snapshot" . PHP_EOL . "+++ Rendered Fusion</>" . PHP_EOL;
    }

    /**
     * get diff of two values
     *
     * @param array $diff
     * @return string
     */
    public function getDiff(array $diff): string
    {
        $buffer = \fopen('php://memory', 'r+b');

        if (0 !== \count($diff)) {
            $this->writeDiffHunks($buffer, $diff);
        }

        $diff = \stream_get_contents($buffer, -1, 0);

        \fclose($buffer);


        if ($diff) {
            $diff = $this->header . $diff;
        }

        return $diff;
    }

    /**
     * @param $output
     * @param array $diff
     */
    private function writeDiffHunks($output, array $diff): void
    {
        $upperLimit = \count($diff);

        // search back for the last `+` and `-` line,
        // check if has trailing linebreak, else add under it warning under it
        $toFind = [1 => true, 2 => true];

        for ($i = $upperLimit - 1; $i >= 0; --$i) {
            if (isset($toFind[$diff[$i][1]])) {
                unset($toFind[$diff[$i][1]]);
                $lc = \substr($diff[$i][0], -1);

                if (PHP_EOL !== $lc) {
                    \array_splice($diff, $i + 1, 0, [[PHP_EOL . "\\ No newline at end of file" . PHP_EOL, Differ::NO_LINE_END_EOF_WARNING]]);
                }

                if (!\count($toFind)) {
                    break;
                }
            }
        }
        // write hunks to output buffer

        $cutOff = \max($this->commonLineThreshold, $this->contextLines);
        $hunkCapture = false;
        $sameCount = $toRange = $fromRange = 0;
        $toStart = $fromStart = 1;

        foreach ($diff as $i => $entry) {

            // Write part of diff if commonLineThreshold is reached (collapse common lines)
            if (0 === $entry[1]) {
                if (false === $hunkCapture) {
                    ++$fromStart;
                    ++$toStart;

                    continue;
                }

                ++$sameCount;
                ++$toRange;
                ++$fromRange;

                if ($sameCount === $cutOff) {
                    $contextStartOffset = ($hunkCapture - $this->contextLines) < 0
                        ? $hunkCapture
                        : $this->contextLines;
                    $this->writeHunk(
                        $diff,
                        $hunkCapture - $contextStartOffset,
                        $i - $cutOff + $this->contextLines + 1,
                        $fromStart - $contextStartOffset,
                        $output
                    );

                    $fromStart += $fromRange;
                    $toStart += $toRange;

                    $hunkCapture = false;
                    $sameCount = $toRange = $fromRange = 0;
                }

                continue;
            }

            $sameCount = 0;

            if ($entry[1] === Differ::NO_LINE_END_EOF_WARNING) {
                continue;
            }

            if (false === $hunkCapture) {
                $hunkCapture = $i;
            }

            if (Differ::ADDED === $entry[1]) {
                ++$toRange;
            }

            if (Differ::REMOVED === $entry[1]) {
                ++$fromRange;
            }
        }

        if (false === $hunkCapture) {
            return;
        }

        // we end here when cutoff (commonLineThreshold) was not reached, but we where capturing a hunk,
        // do not render hunk till end automatically because the number of context lines might be less than the commonLineThreshold

        $contextStartOffset = $hunkCapture - $this->contextLines < 0
            ? $hunkCapture
            : $this->contextLines;

        // prevent trying to write out more common lines than there are in the diff _and_
        // do not write more than configured through the context lines
        $contextEndOffset = \min($sameCount, $this->contextLines);


        $this->writeHunk(
            $diff,
            $hunkCapture - $contextStartOffset,
            $i - $sameCount + $contextEndOffset + 1,
            $fromStart - $contextStartOffset,
            $output
        );
    }

    /**
     * @param array $diff Array of diff
     * @param int $diffStartIndex index to start diff lines
     * @param int $diffEndIndex index to end diff lines
     * @param int $fromStart Line to start diff from
     * @param $output
     */
    private function writeHunk(
        array $diff,
        int $diffStartIndex,
        int $diffEndIndex,
        int $fromStart,
        $output
    ): void
    {

        \fwrite($output, "<fg=cyan>@@ Line: " . $fromStart . " @@</>" . PHP_EOL);

        for ($i = $diffStartIndex; $i < $diffEndIndex; ++$i) {
            if ($diff[$i][1] === Differ::ADDED) {
                \fwrite($output, "<fg=green>" . $diff[$i][0] . "</>");
            } elseif ($diff[$i][1] === Differ::REMOVED) {
                \fwrite($output, "<fg=red>" . $diff[$i][0] . "</>");
            } elseif ($diff[$i][1] === Differ::OLD) {
                \fwrite($output, ' ' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::NO_LINE_END_EOF_WARNING) {
                \fwrite($output, PHP_EOL); // $diff[$i][0]
            } else { /* Not changed (old) Differ::OLD or Warning Differ::DIFF_LINE_END_WARNING */
                \fwrite($output, ' ' . $diff[$i][0]);
            }
        }
    }

}