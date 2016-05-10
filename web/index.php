<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = True;

/* Twig template engine */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates'
));

/* Database */
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => 'dip_dev',
        'user' => 'root',
        'password' => '3091006634',
        'charset' => 'utf8'
    )
));

/* Validation */
$app->register(new Silex\Provider\ValidatorServiceProvider());

/* Test config */
$app['config'] = array(
    'test_questions_count' => 20,
    'test_sentences_count' => 2
);

/* Connect routes */
Sip\Controllers\Routes\setRoutes($app);
$app->run();