<?php

declare(strict_types=1);

namespace Tox82;

/**
 * ServerTiming helper class.
 *
 * Usage:
 * Simply call `ServerTiming::start('metric')` and `ServerTiming::stop('metric')` in your code
 * to measure the time between the two calls.
 * You can have multiple metrics running at the same time.
 * The metrics will be sent to the client in the Server-Timing header.
 * The client can then use this information to display the metrics in the browser.
 * See https://www.w3.org/TR/server-timing/ for more information.
 *
 * You will see the metrics in the Network tab of the Chrome DevTools, under the Timing tab.
 *
 * @category ServerTiming helper class
 * @package  Tox82\ServerTiming
 * @author   Emanuele "ToX" Toscano <https://emanuele.itoscano.com>
 * @license  GNU General Public License; <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */
class ServerTiming
{
    private static $timers = [];
    private static $totals = [];

    /**
     * Starts the timer for the given metric.
     *
     * @param string $metric The name of the metric.
     * @return void
     */
    public static function start(string $metric): void
    {
        // Set the timer for the given metric
        self::$timers[$metric] = microtime(true);

        // If this is the first time we're tracking this metric, initialize the totals array
        if (! isset(self::$totals[$metric])) {
            self::$totals[$metric]['count'] = 0;
            self::$totals[$metric]['time'] = 0;
            self::$totals[$metric]['start'] = microtime(true);
        }
    }

    /**
     * Stops the timer for the given metric and sends the metric to the client in the Server-Timing header.
     *
     * @param string $metric The name of the metric.
     * @return float The duration in milliseconds.
     */
    public static function stop(string $metric): float
    {
        $time = self::$timers[$metric];
        unset(self::$timers[$metric]);

        self::$totals[$metric]['count'] += 1;
        self::$totals[$metric]['time'] += self::getDurationInMicroseconds($time);

        return self::getDurationInMilliseconds($time);
    }

    /**
     * Stops the timer for the given metric if it is running, otherwise starts it.
     *
     * @param string $metric The name of the metric.
     * @return void
     */
    public static function profile(string $metric): void
    {
        if (isset(self::$timers[$metric])) {
            $durationInMicroseconds = self::stop($metric);
            header('Server-Timing: ' . $metric . ';dur=' . $durationInMicroseconds . ';desc=' . $metric);
        } else {
            self::start($metric);
        }
    }

    /**
     * Calculates the time between the start and stop calls and logs the duration.
     *
     * @param string $metric The name of the metric.
     * @return void
     */
    public static function log(string $metric): void
    {
        if (isset(self::$timers[$metric])) {
            $durationInMicroseconds = self::stop($metric);
            $count = self::$totals[$metric]['count'];
            $totalTimeInMicroseconds = self::$totals[$metric]['time'];
            $durationInMilliseconds = self::getDurationInMilliseconds($durationInMicroseconds);
            $totalTimeInMilliseconds = self::getDurationInMilliseconds($totalTimeInMicroseconds);
            error_log(sprintf(
                'Server-Timing: %s stop - (%s ms - called %d times, total time: %s ms)',
                $metric,
                round($durationInMilliseconds, 5),
                $count,
                round($totalTimeInMilliseconds, 5)
            ));
        } else {
            self::start($metric);
            error_log('Server-Timing: ' . $metric . " start");
        }
    }


    /**
     * Calculates the duration in microseconds between the given start time and the current time.
     *
     * @param float $startTime The start time in seconds with microseconds.
     * @return float The duration in microseconds.
     */
    private static function getDurationInMicroseconds(float $startTime): float
    {
        return (microtime(true) - $startTime) * 1000000;
    }

    /**
     * Calculates the duration in milliseconds between the given start time and the current time.
     *
     * @param float $startTime The start time in seconds with microseconds.
     * @return float The duration in milliseconds.
     */
    private static function getDurationInMilliseconds(float $startTime): float
    {
        return self::getDurationInMicroseconds($startTime) / 1000;
    }
}
