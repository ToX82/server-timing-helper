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

    /**
     * Starts the timer for the given metric name
     *
     * @param string $metricName The name of the metric
     * @return void
     */
    public static function start(string $metricName)
    {
        self::$timers[$metricName] = microtime(true);
    }

    /**
     * Stops the timer for the given metric name and sends the metric to the client in the Server-Timing header
     *
     * @param string $metricName The name of the metric
     * @return void
     */
    public static function stop(string $metricName)
    {
        $durationInMicroseconds = (microtime(true) - self::$timers[$metricName]) * 1000;
        header("Server-Timing: " . $metricName . ";dur=" . $durationInMicroseconds . ";desc=" . $metricName);
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
            self::stop($metricName);
        } else {
            self::start($metricName);
        }
    }
}
