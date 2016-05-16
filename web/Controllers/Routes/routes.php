<?php

namespace Sip\Controllers\Routes;

function setRoutes($app)
{
    $authMethod = 'Sip\Controllers\AuthController::checkAuth';

    setAuthRoutes($app);
    setCategoriesRoutes($app, $authMethod);
    setCategoryRoutes($app, $authMethod);
    setQuestionsRoutes($app, $authMethod);
    setQuestionRoutes($app, $authMethod);
    setTestRoutes($app, $authMethod);
}

function setAuthRoutes($app)
{
    $method = 'Sip\Controllers\AuthController::login';
    $app->match('/login/', $method)
        ->method('GET|POST');

    $method = 'Sip\Controllers\AuthController::logout';
    $app->get('/logout/', $method);
}

function setCategoriesRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\CategoriesController::showTree';
    $app->get('/', $method)
        ->before($authMethod);
    $app->get('/grammar-rules/show-tree/', $method)
        ->before($authMethod);

    $method = 'Sip\Controllers\CategoriesController::editTree';
    $app->get('/categories/edit-tree/', $method)
        ->before($authMethod);
}

function setCategoryRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\CategoryController::show';
    $app->get('/grammar-rule/show/{categoryId}/', $method)
        ->assert('categoryId', '\d+')
        ->before($authMethod);
    $app->get('/grammar-rule/show/{categoryId}/{categoryName}/', $method)
        ->assert('categoryId', '\d+')
        ->assert('categoryName', '(\w|-)+')
        ->before($authMethod);

    $method = 'Sip\Controllers\CategoryController::newEdit';
    $app->match('/category/new/', $method)
        ->method('GET|POST')
        ->before($authMethod);
    $app->match('/category/edit/{categoryId}/', $method)
        ->method('GET|POST')
        ->assert('categoryId', '\d+')
        ->before($authMethod);
}

function setQuestionsRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\QuestionsController::show';
    $app->get('/questions/show/', $method)
        ->before($authMethod);
    $app->get('/questions/show/{pageId}/', $method)
        ->assert('pageId', '\d+')
        ->before($authMethod);

    $method = 'Sip\Controllers\QuestionsController::showTree';
    $app->get('/questions/show-tree/', $method)
        ->before($authMethod);
    $app->get('/questions/show-tree/{categoryId}/', $method)
        ->assert('categoryId', '\d+')
        ->before($authMethod);
}

function setQuestionRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\QuestionController::newEdit';
    $app->match('/question/new/', $method)
        ->method('GET|POST')
        ->before($authMethod);
    $app->match('/question/edit/{questionId}/', $method)
        ->method('GET|POST')
        ->assert('questionId', '\d+')
        ->before($authMethod);

    $method = 'Sip\Controllers\QuestionController::getSentences';
    $app->post('/question/get-sentences/', $method)
        ->before($authMethod);
}

function setTestRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\TestController::page';
    $app->get('/test/page/', $method)
        ->before($authMethod);

    $method = 'Sip\Controllers\TestController::start';
    $app->post('/test/start/', $method)
        ->before($authMethod);

    $method = 'Sip\Controllers\TestController::complete';
    $app->post('/test/complete/', $method)
        ->before($authMethod);
}