An experiment in multi-threaded PHP quicksort
=============================================

Using ReactPHP's [Promise](https://reactphp.org/promise/) and [Child Process](https://github.com/reactphp/child-process) to spin up and track additional threads.

Install & run
-------------

* `composer install`
* `php index.php`

Note: If the script doesn't print 'Sorted!' it didn't actually finish, even if you see a time.

Results so far :(
-----------------

With 1 million items and an assumption that 4 threads should work well on my 4-core laptop, my threaded quicksort is so far about 100 times slower than PHP's native `sort()` (based on quicksort).

(1 million items takes about 30 seconds vs. about 0.3.)

Using 4 threads does seem very slightly quicker than 1 with the algorithm otherwise the same, so the main slowdown vs. the native implementation does not appear to be primarily due the threading logic. However it's still disappointing that using threads seems to produce a barely measurable improvement, and that the native implementation is orders of magnitude ahead!

Suggestions as to why and potential improvements are very welcome!

Questions
---------
* Could PHP's [Thread](http://php.net/manual/en/class.thread.php) give a simpler or faster option vs. ReactPHP & its Promises?
* Is there inspiration we could take from PHP's C implementations of the `*sort*()` functions to make the basics quicker while still using PHP?
* Does my naive choice of pivot index (halfway between the previous pointers) make any sense?

Algorithm inspiration
---------------------
Credit for the good parts of the threaded implementation (and no blame for what I've broken) belongs with [this Stack Overflow answer](https://stackoverflow.com/a/3433939/2803757) doing a similar thing in Java.
