<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = True;

$dbOptions = array(
    'driver' => 'pdo_mysql',
    'host' => '127.0.0.1',
    'dbname' => '',
    'user' => '',
    'password' => '',
    'charset' => 'utf8'
);

$configOptions = array(
    'test_questions_count' => 15,
    'test_sentences_count' => 2
);

if (file_exists(__DIR__.'/settings.php')) {
    require_once __DIR__.'/settings.php';
}

/* Twig template engine */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates'
));

/* Database */
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $dbOptions
));

/* Session */
$app->register(new Silex\Provider\SessionServiceProvider());

/* Validation */
$app->register(new Silex\Provider\ValidatorServiceProvider());

/* Test config */
$app['config'] = $configOptions;

/* Connect routes */
\Sip\Controllers\Routes\setRoutes($app);
$app->run();