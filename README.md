# Faktory PHP 5 Library
Faktory job queue library for PHP. Compatible with PHP 5.6 (and maybe lower, but untested)

Most of the code is forked from the [Official PHP library](https://github.com/basekit/faktory_worker_php).

## Why this library, instead of the current official PHP worker libary?
There are a couple reasons:
- If you still work primarily with legacy PHP versions, older than PHP 7. The official library uses some features only available in 7, such as `pcntl_async_signals()`.
- The other library uses the PHP sockets module, which I wanted to avoid, so I'm using Native PHP streams which don't require any extensions to use.

Installation/usage instructions coming soon.
