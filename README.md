# Server Timing Helper
## A Simple Tool for Benchmarking Your PHP Code

The `ServerTiming` class is designed to assist developers in measuring and logging the execution time of various code segments within a PHP application. By utilizing this class, you can initiate and terminate timers for different metrics, transmit these metrics to the client via the Server-Timing header, and record the results to a file. This is particularly useful for performance monitoring and optimization.

![server-timing](https://user-images.githubusercontent.com/659492/216758847-673f1155-db52-48a8-aada-3648b7c837cf.png)

This tool is based on the [Server Timing](https://www.w3.org/TR/server-timing/) specification. You can learn more about it on [web.dev](https://web.dev/custom-metrics/?utm_source=devtools#server-timing-api).

# Installation:
-----------
```shell
composer require tox82/server-timing-helper
```

# Usage
-----------

## The profile Method
This is the easiest way to use the `ServerTiming` class. It automatically handles the start/stop methods, and logs the results to the `Network/Timing` panel in your browser's `DevTools`.

```php
\Tox82\ServerTiming::profile('metricName');
# ... your code here ...
\Tox82\ServerTiming::profile('metricName');
```

## The start/stop Method
Alternatively, you can manually control the start and stop methods by using `start/stop` instead of `profile`:

```php
\Tox82\ServerTiming::start('metricName');
# ... your code here ...
\Tox82\ServerTiming::stop('metricName');
```

## Saving the Output to a Log File
The `log` method enables you to write detailed log entries for your metrics to a file. 
This requires you to have a valid, writeable log file, but IMO it makes it a little bit easier to read the metrics.
You can use nested blocks to log the execution time of specific parts of your code. My suggestion is to add some spaces to the beginning of the nested blocks to make it more readable.

```php
\Tox82\ServerTiming::log('heavyBlocks');
# foreach ($items as $item) {
\Tox82\ServerTiming::log('    singleBlock');
# ... your code here ...
\Tox82\ServerTiming::log('    singleBlock');
# }
\Tox82\ServerTiming::log('heavyBlocks');
```
![server_timing](https://github.com/user-attachments/assets/e8c46310-1671-4239-86a6-7da293e5d371)


## Setting a Custom Log File

By default, `Server Timing Helper` attempts to find the apache error_log file path for logging. However, you can specify a custom log file path using the `setLogFile` method. Make sure to set the log file path before using the `log` method.

```php
\Tox82\ServerTiming::setLogFile('/var/www/server_timing.log');
```

# Resources
---------
 * [Report issues](https://github.com/ToX82/server-timing-helper/issues)
 * [Send Pull Requests](https://github.com/ToX82/server-timing-helper/pulls)
 * [Check the main repository](https://github.com/ToX82/server-timing-helper)
