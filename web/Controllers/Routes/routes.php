<?php

namespace Sip\Controllers\Routes;

function setRoutes($app)
{
    $route = 'Sip\Controllers\CategoriesController::showTree';
    $app->get('/', $route);
    $app->get('/grammar-rules/show-tree/', $route);

    $route = 'Sip\Controllers\CategoriesController::editTree';
    $app->get('/categories/edit-tree/', $route);

    $route = 'Sip\Controllers\CategoryController::show';
    $app->get('/grammar-rule/show/{categoryId}/', $route)
        ->assert('categoryId', '\d+');
    $app->get('/grammar-rule/show/{categoryId}/{categoryName}/', $route)
        ->assert('categoryId', '\d+')
        ->assert('categoryName', '(\w|-)+');

    $route = 'Sip\Controllers\CategoryController::newEdit';
    $app->match('/category/new/', $route)
        ->method('GET|POST');
    $app->match('/category/edit/{categoryId}/', $route)
        ->method('GET|POST')
        ->assert('categoryId', '\d+');

    $route = 'Sip\Controllers\QuestionsController::show';
    $app->get('/questions/show/', $route);
    $app->get('/questions/show/{pageId}/', $route)
        ->assert('pageId', '\d+');

    $route = 'Sip\Controllers\QuestionsController::showTree';
    $app->get('/questions/show-tree/', $route);
    $app->get('/questions/show-tree/{categoryId}/', $route)
        ->assert('categoryId', '\d+');

    $route = 'Sip\Controllers\TestController::page';
    $app->get('/test/page/', $route);

    $route = 'Sip\Controllers\TestController::start';
    $app->post('/test/start/', $route);

    $route = 'Sip\Controllers\TestController::complete';
    $app->post('/test/complete/', $route);
}