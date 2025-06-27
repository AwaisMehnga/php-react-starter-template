<?php
use FastRoute\RouteCollector;

return function (RouteCollector $Route) {
    // Home routes
    $Route->addRoute('GET', '/', 'HomeController@index');
    $Route->addRoute('GET', '/home', 'HomeController@index');

    // User routes
    $Route->addRoute('GET', '/awais', 'UserController@awais');
    $Route->addRoute('GET', '/awais/{name}', 'UserController@awais');
    $Route->addRoute('GET', '/user/{name}', 'UserController@profile');

    // API routes
    $Route->addRoute('GET', '/api', 'HomeController@api');
    $Route->addRoute('GET', '/api/user/{name}', 'UserController@apiGetUser');
    $Route->addRoute('POST', '/api/user', 'UserController@apiCreateUser');

    // Legacy support - direct view includes (for backward compatibility)
    $Route->addRoute('GET', '/legacy/awais', 'views/awais.php');
    $Route->addRoute('GET', '/legacy/awais/{name}', 'views/awais.php');
};
