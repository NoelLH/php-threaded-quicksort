# An experiment in multi-threaded PHP quicksort

Using ReactPHP's [Promise](https://reactphp.org/promise/) and [Child Process](https://github.com/reactphp/child-process) to spin up and track additional threads.

## Results so far :(

With 1 million items and an assumption of 4 threads on my 4 core laptop, my threaded quicksort is so far about 100 times slower than PHP's native `sort()` (based on quicksort).

(1 million items takes about 30 seconds vs. about 0.3.)

Suggestions as to why are very welcome!

## Install & run

* `composer install`
* `php index.php`

Note: If the script doesn't print 'Sorted!' it didn't actually finish...
