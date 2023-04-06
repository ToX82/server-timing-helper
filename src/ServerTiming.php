<?php

declare(strict_types=1);

namespace Tox82;

/**
 * ServerTiming helper class
 * usage: simply call ServerTiming::start('metricName') and ServerTiming::stop('metricName') in your code
 * to measure the time between the two calls
 * You can have multiple metrics running at the same time
 * The metrics will be sent to the client in the Server-Timing header
 * The client can then use this information to display the metrics in the browser
 * See https://www.w3.org/TR/server-timing/ for more information
 *
 * You will see the metrics in the Network tab of the Chrome DevTools, under the Timing tab
 *
 * @category ServerTiming helper class
 * @package  Tox82\ServerTiming
 * @author  Emanuele "ToX" Toscano <https://emanuele.itoscano.com>
 * @license  GNU General Public License; <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */
class ServerTiming
{
    private static $timers = [];
    private static $totals = [];

    /**
     * Starts the timer for the given metric name
     *
     * @param string $metricName The name of the metric
     * @return void
     */
    public static function start(string $metricName)
    {
        self::$timers[$metricName] = microtime(true);

        if (!isset(self::$totals['calls'][$metricName])) {
            self::$totals['calls'][$metricName] = 0;
            self::$totals['time'][$metricName] = 0;
            self::$totals['start'][$metricName] = microtime(true);
        }
    }

    /**
     * Stops the timer for the given metric name and sends the metric to the client in the Server-Timing header
     *
     * @param string $metricName The name of the metric
     * @return float The duration in milliseconds
     */
    public static function stop(string $metricName)
    {
        $time = self::$timers[$metricName];
        unset(self::$timers[$metricName]);

        self::$totals['calls'][$metricName] += 1;
        self::$totals['time'][$metricName] += (microtime(true) - $time) * 1000;

        return (microtime(true) - $time) * 1000;
    }

    /**
     * Stops the timer for the given metric name if it is running, otherwise starts it
     *
     * @param string $metricName The name of the metric
     * @return void
     */
    public static function profile(string $metricName)
    {
        if (isset(self::$timers[$metricName])) {
            $durationInMicroseconds = self::stop($metricName);
            header("Server-Timing: " . $metricName . ";dur=" . $durationInMicroseconds . ";desc=" . $metricName);
        } else {
            self::start($metricName);
        }
    }

    /**
     * Log method: this calculates the time between the start and stop calls, and triggers an error_log for both the start and the stop times, with the time difference in the stop message
     *
     * @param string $metricName The name of the metric
     * @return void
     */
    public static function log(string $metricName)
    {
        if (isset(self::$timers[$metricName])) {
            $durationInMicroseconds = self::stop($metricName);
            $totalCalls = self::$totals['calls'][$metricName];
            $totalInMicroseconds = self::$totals['time'][$metricName];
            error_log('Server-Timing: ' . $metricName . " stop - (" . round($durationInMicroseconds, 5) . " ms - called " . $totalCalls . " times, total time: " . round($totalInMicroseconds, 5) . " ms)");
        } else {
            self::start($metricName);
            error_log('Server-Timing: ' . $metricName . " start");
        }
    }
}
