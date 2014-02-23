<?php
$app->container->singleton('HomeController', function () {
    return new HomeController();
});

// routes
$app->get('/', function () use ($app) {
    $app->HomeController->index();
})->name('home');