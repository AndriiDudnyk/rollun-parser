<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

use Parser\LoaderManager;
use Psr\Container\ContainerInterface;
use rollun\datastore\Middleware\DataStoreApi;
use rollun\permission\ConfigProvider;
use rollun\permission\OAuth\LoginMiddleware;
use rollun\permission\OAuth\LogoutMiddleware;
use rollun\permission\OAuth\RedirectMiddleware;
use rollun\permission\OAuth\RegisterMiddleware;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 *
 * @param Application $app
 * @param MiddlewareFactory $factory
 * @param ContainerInterface $container
 * @return void
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get(
        '/oauth/redirect',
        RedirectMiddleware::class,
        'oauth-redirect'
    );

    $app->get(
        '/oauth/login',
        LoginMiddleware::class,
        ConfigProvider::OAUTH_LOGIN_ROUTE_NAME
    );

    $app->get(
        '/oauth/register',
        RegisterMiddleware::class,
        ConfigProvider::OAUTH_REGISTER_ROUTE_NAME
    );

    $app->get(
        '/logout',
        LogoutMiddleware::class,
        'logout'
    );

    $app->route(
        '/api/datastore[/{resourceName}[/{id}]]',
        DataStoreApi::class,
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        DataStoreApi::class
    );

    $app->get(
        '/parse',
        LoaderManager::class,
        LoaderManager::class
    );
};
