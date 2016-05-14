<?php

namespace Sip\Controllers\Routes;

function setRoutes($app)
{
    setAuthRoutes($app);
    setCategoriesRoutes($app);
    setCategoryRoutes($app);
    setQuestionsRoutes($app);
    setQuestionRoutes($app);
    setTestRoutes($app);
}

function setAuthRoutes($app)
{
    $route = 'Sip\Controllers\AuthController::login';
    $app->match('/login/', $route)
        ->method('GET|POST');

    $route = 'Sip\Controllers\AuthController::logout';
    $app->get('/logout/', $route);
}

function setCategoriesRoutes($app)
{
    $route = 'Sip\Controllers\CategoriesController::showTree';
    $app->get('/', $route);
    $app->get('/grammar-rules/show-tree/', $route);

    $route = 'Sip\Controllers\CategoriesController::editTree';
    $app->get('/categories/edit-tree/', $route);
}

function setCategoryRoutes($app)
{
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
}

function setQuestionsRoutes($app)
{
    $route = 'Sip\Controllers\QuestionsController::show';
    $app->get('/questions/show/', $route);
    $app->get('/questions/show/{pageId}/', $route)
        ->assert('pageId', '\d+');

    $route = 'Sip\Controllers\QuestionsController::showTree';
    $app->get('/questions/show-tree/', $route);
    $app->get('/questions/show-tree/{categoryId}/', $route)
        ->assert('categoryId', '\d+');
}

function setQuestionRoutes($app)
{
    $route = 'Sip\Controllers\QuestionController::newEdit';
    $app->match('/question/new/', $route)
        ->method('GET|POST');
    $app->match('/question/edit/{questionId}/', $route)
        ->method('GET|POST')
        ->assert('questionId', '\d+');

    $route = 'Sip\Controllers\QuestionController::getSentences';
    $app->post('/question/get-sentences/', $route);
}

function setTestRoutes($app)
{
    $route = 'Sip\Controllers\TestController::page';
    $app->get('/test/page/', $route);

    $route = 'Sip\Controllers\TestController::start';
    $app->post('/test/start/', $route);

    $route = 'Sip\Controllers\TestController::complete';
    $app->post('/test/complete/', $route);
}