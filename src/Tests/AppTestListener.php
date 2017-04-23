<?php

namespace DigitalDjPoolBundle\Tests;

use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestResult;
use PHPUnit_Framework_TestSuite;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class AppTestListener.
 */
class AppTestListener extends \PHPUnit_TextUI_ResultPrinter implements \PHPUnit_Framework_TestListener
{
    private $incomplete = [];

    private $succes = [];

    private $error = [];

    private $total = [];

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        parent::addError($test, $e, $time);
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        parent::addFailure($test, $e, $time);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        /** @var \PHPUnit_Framework_TestCase $test */
        if (is_subclass_of($test, 'PHPUnit_Framework_TestCase')) {
            $this->incomplete['_'] = isset($this->incomplete['_']) ? $this->incomplete['_'] + 1 : 1;
            foreach ($this->getGroups($test->getAnnotations()) as $group) {
                $this->incomplete[$group] = isset($this->incomplete[$group]) ? $this->incomplete[$group] + 1 : 1;
            }
        }

        parent::addIncompleteTest($test, $e, $time);
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        parent::addRiskyTest($test, $e, $time);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        parent::addSkippedTest($test, $e, $time);
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::startTestSuite($suite);
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::endTestSuite($suite);
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        parent::startTest($test);
        if (is_subclass_of($test, 'PHPUnit_Framework_TestCase')) {
            $this->total['_'] = isset($this->total['_']) ? $this->total['_'] + 1 : 1;
            foreach ($this->getGroups($test->getAnnotations()) as $group) {
                $this->total[$group] = isset($this->total[$group]) ? $this->total[$group] + 1 : 1;
            }
        }
    }

    /**
     * @param array $annotations
     * @param null  $part
     *
     * @return array
     */
    private function getGroups($annotations = [], $part = null)
    {
        if ($part) {
            if (isset($annotations[$part]['group'])) {
                return $annotations[$part]['group'];
            }
        } else {
            return array_merge(
                $this->getGroups($annotations, 'method'),
                $this->getGroups($annotations, 'class')
            );
        }

        return [];
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        /** @var \PHPUnit_Framework_TestCase $test */
        if (is_subclass_of($test, 'PHPUnit_Framework_TestCase')) {
            if ($test->getStatus()) {
                $this->succes['_'] = isset($this->succes['_']) ? $this->succes['_'] + 1 : 1;

                foreach ($this->getGroups($test->getAnnotations()) as $group) {
                    $this->succes[$group] = isset($this->succes[$group]) ? $this->succes[$group] + 1 : 1;
                }
            }
        }
        parent::endTest($test, $time);
    }

    /**
     * @param PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        parent::printResult($result);
        $this->printAdditionalData();
    }

    private function printAdditionalData()
    {
        $out = new ConsoleOutput();
        $table = new Table($out);

        $table->addRow(['**Group**', 'Incomplete', 'Total']);

        foreach ($this->incomplete as $group => $val) {
            $table->addRow(
                [
                    $group,
                    sprintf('%s (%s)', $this->incomplete[$group].'/'.$this->total[$group], $this->percentCalc($this->incomplete[$group], $this->total[$group])),
                    sprintf('%s (%s)', $this->total[$group] - $this->incomplete[$group].'/'.$this->total[$group], $this->percentCalc($this->total[$group] - $this->incomplete[$group], $this->total[$group])),
                ]
            );
        }
        $table->render();
        $table->setRows([]);
        $table->addRow([new TableCell('**Total**', ['colspan' => 3])]);
        foreach ($this->total as $group => $val) {
            if ('_' === $group) {
                continue;
            }
            $table->addRow(
                [$group, $this->total[$group].'/'.$this->total['_'], $this->percentCalc($this->total[$group], $this->total['_'])]
            );
        }
        $table->render();
    }

    /**
     * @param $current
     * @param $total
     *
     * @return string
     */
    private function percentCalc($current, $total)
    {
        if ($total > 0) {
            return number_format(($current / $total) * 100, 2).' %';
        }

        return 0 .' %';
    }
}
