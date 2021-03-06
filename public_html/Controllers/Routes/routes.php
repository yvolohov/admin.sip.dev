<?php

namespace Sip\Controllers\Routes;

function setRoutes($app)
{
    $authMethod = 'Sip\Controllers\AuthController::checkAuth';
    $twigGlobalsMethod = 'Sip\Controllers\AuthController::setTwigGlobals';

    $app->before($twigGlobalsMethod);
    setAuthRoutes($app);
    setCategoriesRoutes($app, $authMethod);
    setCategoryRoutes($app, $authMethod);
    setQuestionsRoutes($app, $authMethod);
    setQuestionRoutes($app, $authMethod);
    setTestRoutes($app, $authMethod);
    setAssistantRoutes($app, $authMethod);
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
    $app->get('/', $method);
    $app->get('/grammar-rules/show-tree/', $method);

    $method = 'Sip\Controllers\CategoriesController::editTree';
    $app->get('/categories/edit-tree/', $method)
        ->before($authMethod);
}

function setCategoryRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\CategoryController::show';
    $app->get('/grammar-rule/show/{categoryId}/', $method)
        ->assert('categoryId', '\d+');
    $app->get('/grammar-rule/show/{categoryId}/{categoryName}/', $method)
        ->assert('categoryId', '\d+')
        ->assert('categoryName', '(\w|-)+');

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
    $app->get('/test/start/', $method)
        ->before($authMethod);
    $app->get('/test/start/{categoryId}/', $method)
        ->assert('categoryId', '\d+')
        ->before($authMethod);

    $method = 'Sip\Controllers\TestController::complete';
    $app->get('/test/complete/', $method)
        ->before($authMethod);
    $app->get('/test/complete/{categoryId}/', $method)
        ->assert('categoryId', '\d+')
        ->before($authMethod);
}

function setAssistantRoutes($app, $authMethod)
{
    $method = 'Sip\Controllers\AssistantController::assistant';
    $app->get('/assistant/', $method)
        ->before($authMethod);
}