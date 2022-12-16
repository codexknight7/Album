<?php

namespace Album;

// Add these import statements:
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;


class Module implements ConfigProviderInterface
{

    // getConfig() method is here
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    // Add this method:
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\AlbumTable::class => function ($container) {
                    $tableGateway = $container->get(Model\AlbumTableGateway::class);
                    return new Model\AlbumTable($tableGateway);
                },
                Model\AlbumTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Album());
                    return new TableGateway('album', $dbAdapter, null, $resultSetPrototype);
                },
                Model\GenreTable::class => function ($container) {
                    $tableGateway = $container->get(Model\GenreTableGateway::class);
                    return new Model\GenreTable($tableGateway);
                },
                Model\GenreTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Genre());
                    return new TableGateway('genre', $dbAdapter, null, $resultSetPrototype);
                },
                Controller\AlbumController::class => function ($container) {
                    return new Controller\AlbumController(
                        $container->get(Model\AlbumTable::class),
                        $container->get(Laminas\Form\FormElementManager::class)
                    );
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\AlbumController::class => ReflectionBasedAbstractFactory::class,
            ],
        ];
    }
}