<?php

declare(strict_types=1);

// Keep credentials outside the source tree (environment variables).
define('AppName', getenv('APP_NAME') ?: 'Demo');
define('DatabaseServer', getenv('DB_HOST') ?: '127.0.0.1');
define('Database', getenv('DB_NAME') ?: 'dbdemo');
define('DatabaseUser', getenv('DB_USER') ?: '');
define('DatabasePassword', getenv('DB_PASSWORD') ?: '');
