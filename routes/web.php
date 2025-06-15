<?php
use FastRoute\RouteCollector;

return function (RouteCollector $Route) {
    $Route->addRoute('GET', '/', 'views/index.php');
    $Route->addRoute('GET', '/awais', 'views/awais.php');
    $Route->addRoute('GET', '/awais/{name}', 'views/awais.php'); // dynamic parameter
};
