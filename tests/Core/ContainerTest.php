<?php

use PHPUnit\Framework\TestCase;
use Core\Container;
use Core\Logger;
use Core\UserService;

class ContainerTest extends TestCase
{
    private $container;

    protected function setUp(): void
    {
        // Создаём новый контейнер перед каждым тестом
        $this->container = new Container();
    }


    // Тест для регистрации и извлечения обычного сервиса

    /**
     * @throws Exception
     */
    public function testRegisterAndGetService()
    {
        // Регистрация сервиса
        $this->container->register('Logger', new Logger());

        // Извлечение сервиса
        $logger = $this->container->get('Logger');

        // Проверка, что извлечённый объект - это экземпляр Logger
        $this->assertInstanceOf(Logger::class, $logger);
    }

    /**
     * @throws Exception
     */
    public function testSingletonService()
    {
        // Регистрация синглтона
        $this->container->singleton('singletonLogger', function ()
        {
            return new Logger();
        });

        // Извлечение синглтона
        $logger1 = $this->container->get('singletonLogger');
        $logger2 = $this->container->get('singletonLogger');

        // Проверка, что оба объекта - это один и тот же экземпляр
        $this->assertSame($logger1, $logger2);
    }
    
    
    // Тест для фабрики

    /**
     * @throws Exception
     */
    public function testFactoryService()
    {
        $this->container->register('Logger', new Logger());
        // Регистрация фабрики
        $this->container->bind('userService', function ($container)
        {
            return new UserService($container->get('Logger'));
        });

        // Извлечение объекта через фабрику
        $userService = $this->container->get('userService');

        // Проверка, что это экземпляр UserService
        $this->assertInstanceOf(UserService::class, $userService);
    }
    
    
    // Тест для псевдонима

    /**
     * @throws Exception
     */
    public function testAliasService()
    {
        // Регистрация сервиса
        $this->container->register('Logger', new Logger());

        // Создание псевдонима
        $this->container->alias('log', 'Logger');

        // Извлечение сервиса через псевдоним
        $logger = $this->container->get('log');

        // Проверка, что экземпляр Logger
        $this->assertInstanceOf(Logger::class, $logger);
    }


    // Проверка на несуществующий сервис
    public function testGetNonExistingService()
    {
        // Ожидаем исключения, если сервис не найден
        $this->expectException(Exception::class);
        $this->container->get('nonExistingService');
    }
}
