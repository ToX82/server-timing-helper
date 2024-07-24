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
    private static $logFile;

    /**
     * Attempts to detect a suitable log file path based on common locations.
     * 
     * This method iterates through a list of common log file paths to find the first one that exists and is writable.
     * If no suitable log file is found, $logFile is set to null.
     * 
     * @return void
     */
    public static function detectLogFile(): void
    {
        // Common log file paths
        $commonLogPaths = [
            '/var/log/apache2/error.log',
            '/var/log/httpd/error.log',
            'C:/Apache24/logs/error.log',
            '/Applications/MAMP/logs/apache_error.log',
            '/Applications/MAMP/logs/error.log',
        ];

        foreach ($commonLogPaths as $path) {
            if (file_exists($path) && is_writable($path)) {
                self::$logFile = $path;
                return;
            }
        }

        self::$logFile = null;
    }

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
            self::$totals[$metric] = ['count' => 0, 'time' => 0, 'start' => microtime(true)];
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
        $duration = self::getDuration($metric);
        self::$totals[$metric]['count'] += 1;
        self::$totals[$metric]['time'] += $duration['microseconds'];

        // The metric name is used as is in the Server-Timing header, so we need to sanitize it
        // to avoid issues with special characters.
        $metric = preg_replace('/[^a-zA-Z0-9\s]/', '', $metric);
        $metric = str_replace(' ', '_', $metric);

        header('Server-Timing: ' . $metric . ';dur=' . $duration['milliseconds'] . ';', false);
        
        return $duration['milliseconds'];
    }

    /**
     * Stops the timer for the given metric if it is running, otherwise starts it.
     *
     * @param string $metric The name of the metric.
     * @return void
     */
    public static function profile(string $metric): void
    {
        if (!isset(self::$timers[$metric])) {
            self::start($metric);
            return;
        }

        $duration = self::stop($metric);
    }

    /**
     * Calculates the time between the start and stop calls and logs the duration.
     *
     * @param string $metric The name of the metric.
     * @param bool $debug If true, the start time will be printed.
     * @return void
     */
    public static function log(string $metric, bool $debug = true): void
    {
        if (!self::$logFile) {
            self::detectLogFile();
        }

        if (!isset(self::$timers[$metric])) {
            self::start($metric);
            if ($debug) {
                self::writeLog('Server-Timing: ' . $metric);
            }
            return;
        }

        $duration = self::stop($metric);
        $count = self::$totals[$metric]['count'];
        $totalTime = self::getDurationInMilliseconds(self::$totals[$metric]['time']);

        self::writeLog(sprintf(
            'Server-Timing: %s - (%s ms - called %d times, total time: %s ms)',
            $metric,
            round($duration, 5),
            $count,
            round($totalTime, 5)
        ));
    }

    /**
     * Writes a message to the log file.
     *
     * @param string $message The message to be written to the log file.
     * @return void
     */
    private static function writeLog(string $message): void
    {
        file_put_contents(self::$logFile, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Sets the path to the log file.
     *
     * This method sets the path to the log file where the ServerTiming metrics will be written.
     * It checks if the file exists and if it is writable. If the file does not exist or is not writable,
     * it throws an exception.
     *
     * @param string $path The path to the log file.
     * @return void
     * @throws \Exception If the file does not exist or is not writable.
     */
    public static function setLogFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new \Exception("ServerTiming exception: The file does not exist: $path");
        }
        if (!is_writable($path)) {
            throw new \Exception("ServerTiming exception: The file exists but is not writable: $path");
        }

        self::$logFile = $path;
    }

    /**
     * Calculates the duration in microseconds and milliseconds between the given start time and the current time.
     *
     * @param string $metric The name of the metric.
     * @return array The duration in microseconds and milliseconds.
     */
    private static function getDuration(string $metric): array
    {
        $startTime = self::$timers[$metric];
        unset(self::$timers[$metric]);

        $durationInMicroseconds = (microtime(true) - $startTime) * 1000000;
        return [
            'microseconds' => $durationInMicroseconds,
            'milliseconds' => $durationInMicroseconds / 1000
        ];
    }

    /**
     * Calculates the duration in milliseconds between the given start time and the current time.
     *
     * @param float $microseconds The duration in microseconds.
     * @return float The duration in milliseconds.
     */
    private static function getDurationInMilliseconds(float $microseconds): float
    {
        return $microseconds / 1000;
    }
}
