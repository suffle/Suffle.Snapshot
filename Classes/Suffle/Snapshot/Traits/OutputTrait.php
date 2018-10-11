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

use Neos\Flow\Annotations as Flow;

/**
 * Utility trait to generate console outputs
 */
trait OutputTrait
{
    static protected $INFO_COLOR = "yellow";
    static protected $SUCCESS_COLOR = "green";
    static protected $FAILED_COLOR = "red";

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Cli\ConsoleOutput
     */
    protected $output;

    /**
     * Outputs specified text to the console window in color yellow
     * You can specify arguments that will be passed to the text via sprintf
     *
     *
     * @param string $text Text to output
     * @param int tabs number of tab indentations
     */
    protected function outputInfoText(string $text, int $tabs = 0): void
    {
        $text = "<fg=" . SELF::$INFO_COLOR . ">" . $text . "</>" . PHP_EOL;
        $this->outputTabbed($text, [], $tabs);
    }

    /**
     * Outputs text to the console with INFO prefix
     * You can specify arguments that will be passed to the text via sprintf
     *
     *
     * @param string $text Text to output
     * @param array $arguments Optional arguments to use for sprintf
     * @param int tabs number of tab indentations
     */
    protected function outputInfo(string $text, array $arguments = [], int $tabs = 0): void
    {
        $this->outputPrefixed('INFO', SELF::$INFO_COLOR, $text, $arguments, $tabs);
    }

    /**
     * Outputs text to the console with SUCCESS prefix
     * You can specify arguments that will be passed to the text via sprintf
     *
     *
     * @param string $text
     * @param array $arguments
     * @param int $tabs
     */
    protected function outputSuccess(string $text, array $arguments = [], int $tabs = 0): void
    {
        $this->outputPrefixed("SUCCESS", SELF::$SUCCESS_COLOR, $text, $arguments, $tabs);
    }

    /**
     * Outputs text to the console with FAILED prefix
     * You can specify arguments that will be passed to the text via sprintf
     *
     *
     * @param string $text
     * @param array $arguments
     * @param int $tabs
     */
    protected function outputFailed(string $text, array $arguments = [], int $tabs = 0): void
    {
        $this->outputPrefixed('FAILED', SELF::$FAILED_COLOR, $text, $arguments, $tabs);
    }

    /**
     * Outputs new line
     */
    protected function outputNewLine(): void
    {
        $this->output(PHP_EOL);
    }

    /**
     * Outputs indented text
     * You can specify arguments that will be passed to the text via sprintf
     *
     *
     * @param string $text
     * @param array $arguments
     * @param int $tabs
     */
    protected function outputTabbed(string $text, array $arguments = [], int $tabs = 0): void
    {
        $lines = explode(PHP_EOL, $text);

        foreach ($lines as $line) {
            if ($line) {
                $output = str_repeat("\t", $tabs) . $line . PHP_EOL;
                $this->output($output, $arguments);
            }
        }
    }


    /**
     * @param string $prefix
     * @param string $color
     * @param string $text
     * @param array $arguments
     * @param int $tabs
     */
    private function outputPrefixed(string $prefix, string $color, string $text, array $arguments = [], int $tabs = 0): void
    {
        $formattedText = "<fg=" . $color . ">" . $prefix . "</>\t" . $text . PHP_EOL;
        $this->outputTabbed($formattedText, $arguments, $tabs);
    }


    /**
     * @param string $text
     * @param array $arguments
     */
    protected function output(string $text, array $arguments = []): void
    {
        $this->output->output($text, $arguments);
    }

    /**
     * Outputs a question to the user and checks, if the answer is valid.
     * If invalid it prints a list of possible answers and explanation
     *
     * @param string $question
     * @param array $answers in form of answer => explanation
     * @param string  $default default value if user presses enter without an answer (can't be null => throws Exception)
     * @param int $tabs
     * @return string
     */
    protected function waitAndAsk(string $question, array $answers, string $default, int $tabs = 0): string
    {
        $response = null;
        $formattedQuestion = str_repeat("\t", $tabs) . "<fg=cyan>" . $question . "</>";
        $answersShort = "[" . implode(',', array_keys($answers)) . "]";

        while (!array_key_exists($response, $answers)) {
            if ($response !== null) {
                $this->outputNewLine();

                foreach ($answers as $abbr => $answer) {
                    $answerString = str_repeat("\t", $tabs) . "<fg=yellow>" . $abbr . "</> - " . $answer;
                    $this->output($answerString . PHP_EOL);
                }
            }

            $response = strtolower($this->output->ask("<fg=cyan>" . $formattedQuestion . " " . $answersShort .  "</>", $default));
        }
        $this->output->outputLine();

        return $response;
    }

}
