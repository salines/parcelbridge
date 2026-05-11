<?php
/**
 * Routes configuration.
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/login', ['controller' => 'Users', 'action' => 'login'], ['_name' => 'login']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout'], ['_name' => 'logout']);
        $builder->connect('/forgot-password', ['controller' => 'Users', 'action' => 'forgotPassword'], ['_name' => 'password:forgot']);
        $builder->connect('/reset-password/{token}', ['controller' => 'Users', 'action' => 'resetPassword'], ['_name' => 'password:reset'])
            ->setPass(['token']);
        $builder->connect('/account', ['controller' => 'Users', 'action' => 'profile'], ['_name' => 'account']);
        $builder->connect('/account/edit', ['controller' => 'Users', 'action' => 'edit'], ['_name' => 'account:edit']);
        $builder->connect('/account/password', ['controller' => 'Users', 'action' => 'changePassword'], ['_name' => 'account:password']);
        $builder->connect('/admin/logout', ['controller' => 'Users', 'action' => 'logout'], ['_name' => 'admin:logout']);
        $builder->connect('/client/logout', ['controller' => 'Users', 'action' => 'logout'], ['_name' => 'client:logout']);

        $builder->connect('/', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/pages/*', 'Pages::display');

        $builder->fallbacks();
    });

    $routes->prefix('Admin', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->fallbacks();
    });

    $routes->prefix('Client', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->fallbacks();
    });

    $routes->prefix('Api', ['path' => '/api'], function (RouteBuilder $builder): void {
        $builder->setExtensions(['json']);
        $builder->connect('/login', ['controller' => 'Users', 'action' => 'login'])
            ->setMethods(['POST']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout'])
            ->setMethods(['POST', 'DELETE']);
        $builder->connect('/me', ['controller' => 'Users', 'action' => 'me'])
            ->setMethods(['GET']);

        $builder->connect('/packages', ['controller' => 'Packages', 'action' => 'index'])
            ->setMethods(['GET']);
        $builder->connect('/packages/{id}', ['controller' => 'Packages', 'action' => 'view'])
            ->setPass(['id'])
            ->setMethods(['GET']);
        $builder->connect('/packages/{id}/invoice', ['controller' => 'Packages', 'action' => 'uploadInvoice'])
            ->setPass(['id'])
            ->setMethods(['POST', 'PUT']);
        $builder->connect('/packages/{id}/ready-for-pickup', ['controller' => 'Packages', 'action' => 'readyForPickup'])
            ->setPass(['id'])
            ->setMethods(['POST']);
        $builder->connect('/packages/{id}/deliver', ['controller' => 'Packages', 'action' => 'deliver'])
            ->setPass(['id'])
            ->setMethods(['POST']);

        $builder->connect('/invoices', ['controller' => 'Invoices', 'action' => 'index'])
            ->setMethods(['GET']);
        $builder->connect('/invoices/{id}', ['controller' => 'Invoices', 'action' => 'view'])
            ->setPass(['id'])
            ->setMethods(['GET']);
        $builder->connect('/invoices/{id}/approve', ['controller' => 'Invoices', 'action' => 'approve'])
            ->setPass(['id'])
            ->setMethods(['POST']);
        $builder->connect('/invoices/{id}/needs-review', ['controller' => 'Invoices', 'action' => 'needsReview'])
            ->setPass(['id'])
            ->setMethods(['POST']);

        $builder->connect('/ship-requests', ['controller' => 'ShipRequests', 'action' => 'index'])
            ->setMethods(['GET']);
        $builder->connect('/ship-requests', ['controller' => 'ShipRequests', 'action' => 'add'])
            ->setMethods(['POST']);
        $builder->connect('/ship-requests/{id}', ['controller' => 'ShipRequests', 'action' => 'view'])
            ->setPass(['id'])
            ->setMethods(['GET']);
        $builder->connect('/ship-requests/{id}/process', ['controller' => 'ShipRequests', 'action' => 'process'])
            ->setPass(['id'])
            ->setMethods(['POST']);

        $builder->connect('/shipments', ['controller' => 'Shipments', 'action' => 'index'])
            ->setMethods(['GET']);
    });
};
