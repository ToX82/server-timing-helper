# Server Timing Helper
## A simple tool that you can use to benchmark your PHP code

This tool provides an easy way to benchmark your PHP code. It is based on the [Server Timing](https://www.w3.org/TR/server-timing/) specification. You can read more about it on [web.dev](https://web.dev/custom-metrics/?utm_source=devtools#server-timing-api).

Installation:
```shell
composer require tox82/server-timing-helper
```

Usage
-----------

Let's say you want to benchmark the execution of a function. You can do it like this:

```php
\Tox82\ServerTiming::profile('metricName');
# ... your code here ...
\Tox82\ServerTiming::profile('metricName');
```

And that's it! You will see the metrics in the Network tab of your browser's DevTools, under the Timing tab

![server-timing](https://user-images.githubusercontent.com/659492/216758847-673f1155-db52-48a8-aada-3648b7c837cf.png)

I don't like the ServerTiming profiler!
-----------

You can track the metrics in your log files too. Just use the `log` method instead:

```php
\Tox82\ServerTiming::log('metricName');
# ... your code here ...
\Tox82\ServerTiming::log('metricName');
```


Resources
---------
 * [Report issues](https://github.com/ToX82/server-timing-helper/issues)
 * [Send Pull Requests](https://github.com/ToX82/server-timing-helper/pulls)
 * [Check the main repository](https://github.com/ToX82/server-timing-helper)
