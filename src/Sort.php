<?php
namespace Quicksort;

use React\ChildProcess\Process;
use React\Promise\Deferred;

/**
 * A so far very un-performant attempt to do a multi-threaded quicksort using ReactPHP.
 *
 * Some key logic adapted from this Java answer
 * @link https://stackoverflow.com/a/3433939/2803757
 */
class Sort
{
    /** @var int */
    private static $maxThreads = 4;  // I have 4 cores total but let's try this out..
    /** @var int Number of threads we need results from before we're done. */
    private static $waitingNum = 0;
    /** @var int[] */
    private $array;
    /** @var int */
    private $arraySize;
    /** @var int */
    private $minPartitionSize;
    /** @var bool Print detail on each pass - not recommended for large arrays! */
    private $debug = false;
    /** @var Deferred */
    private $rootDeferred;

    /**
     * @param int[] $randomArray
     */
    public function __construct(array $randomArray)
    {
        $this->array = $randomArray;
        $this->arraySize = count($randomArray);
    }

    public function start()
    {
        $this->minPartitionSize = $this->arraySize / static::$maxThreads;

        if ($this->debug) {
            echo 'Min partition size: ' . $this->minPartitionSize;
        }

        $this->rootDeferred = new Deferred();
        $this->rootDeferred->promise()->then(
            function() {
                echo 'Sorted!';
                if ($this->debug) {
                    var_dump($this->array);
                }
            },
            null,
            function() {
                static::$waitingNum--;

                if ($this->debug) {
                    echo PHP_EOL . 'Got progress! We now have ' . static::$waitingNum . ' threads left';
                }

                if (static::$waitingNum === 0) {
                    $this->rootDeferred->resolve();
                }
            }
        );

        $this->doSort(0, $this->arraySize - 1, $this->rootDeferred);
    }

    /**
     * @param int           $start      Index of first element to consider
     * @param int           $end        Index of last element to consider
     * @param Deferred|null $deferred   Resolved when input section is sorted
     */
    public function doSort(int $start, int $end, Deferred $deferred = null): void
    {
        if ($deferred) {
            static::$waitingNum++;
            if ($this->debug) {
                echo PHP_EOL . 'We now have ' . static::$waitingNum . ' threads in progress';
            }
        }

        if ($this->debug) {
            echo PHP_EOL;
            if ($deferred) {
                echo "New thread sorting with $start, $end";
            } else {
                echo "Synchronously sorting with $start, $end";
            }
        }

        $length = $end - $start + 1;

        if ($length <= 1) {
            if ($deferred) {
                if ($this->debug) {
                    echo PHP_EOL . 'Notifying! (no items to check in this range)';
                }
                $deferred->notify();
            }

            return;
        }

        $pivotIndex = floor(($end - $start) / 2) + $start;
        $pivotValue = $this->array[$pivotIndex];

        $this->swap($pivotIndex, $end);

        $storeIndex = $start;
        for ($ii = $start; $ii < $end; $ii++) {
            if ($this->array[$ii] <= $pivotValue) {
                $this->swap($ii, $storeIndex);
                $storeIndex++;
            }
        }

        $this->swap($storeIndex, $end);

        if ($length > $this->minPartitionSize) {
            // Do the left side spawning a new thread, passing $deferred to keep track
            // TODO We must have a race condition here where we could accidentally stop early. The thread
            // counter increment ought to happen first.
            new Process($this->doSort($start, $storeIndex - 1, $deferred));

            // Do right hand side synchronously
            $this->doSort($storeIndex + 1, $end);

            // LHS completion is tracked by incrementing the thread counter and making the thread responsible
            // for its own notify call.  RHS is synchronous so if we're here it's time for this thread to
            // notify and decrement the outstanding work counter by 1.
            if ($deferred) {
                if ($this->debug) {
                    echo PHP_EOL . 'Notifying! (done synchronous RHS and spawned next LHS)';
                }
                $deferred->notify();
            }
        } else {
            // No new threads / promises - all synchronous, notify / decrement counter when both parts done.
            $this->doSort($start, $storeIndex - 1);
            $this->doSort($storeIndex + 1, $end);
            if ($deferred) {
                if ($this->debug) {
                    echo PHP_EOL . 'Notifying! (no new thread, synchronous done)';
                }
                $deferred->notify();
            }
        }
    }

    public static function getRandomisedArray(int $elements): array
    {
        $array = [];
        for ($ii = 0; $ii < $elements; $ii++) {
            $array[] = $ii;
        }
        shuffle($array);

        return $array;
    }

    private function swap(int $indexA, int $indexB): void
    {
        $valueA = $this->array[$indexA];
        $this->array[$indexA] = $this->array[$indexB];
        $this->array[$indexB] = $valueA;
    }
}
