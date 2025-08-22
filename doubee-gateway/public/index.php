<?php

declare (strict_types=1);

namespace think;

define('BASE_PATH', dirname(__DIR__, 1));
require BASE_PATH . '/vendor/autoload.php';

(function () {
    $http = (new App())->http;
    $response = $http->run();
    $response->send();
    $http->end($response);
})();