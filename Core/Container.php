<?php

namespace Core;

class Container {
    private $services = [];     // Обычные сервисы
    private $instances = [];    // Синглтоны
    private $factories = [];    // Фабрики
    private $aliases = [];      // Псевдонимы

    // Регистрируем обычный сервис
    public function register($name, $service) {
        $this->services[$name] = $service;
    }

    // Регистрируем фабрику для отложенной загрузки
    public function bind($name, $callback) {
        $this->factories[$name] = $callback;
    }

    // Регистрируем синглтон (экземпляр, который будет создан один раз)
    public function singleton($name, $callback) {
        $this->bind($name, function($container) use ($callback) {
            static $instance = null;
            if ($instance === null) {
                $instance = $callback($container);
            }
            return $instance;
        });
    }

    // Регистрируем псевдоним для интерфейсов или классов
    public function alias($alias, $original) {
        $this->aliases[$alias] = $original;
    }

    // Извлекаем сервис из контейнера

    /**
     * @throws \Exception
     */
    public function get($name) {
        // Проверка на алиас
        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }

        // Проверка на синглтон
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Проверка на фабрику (ленивая загрузка)
        if (isset($this->factories[$name])) {
            $object = $this->factories[$name]($this);
            return $object;
        }

        // Проверка на обычный сервис
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        // Если ничего не найдено
        throw new \Exception("Service '$name' not found.");
    }

    // Создаем объект через инжекцию зависимостей

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function make($className) {
        $reflector = new \ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class '$className' is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // Если у класса есть конструктор, получаем его параметры
        if ($constructor) {
            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $dependency = $parameter->getClass();

                if ($dependency) {
                    $dependencies[] = $this->get($dependency->name); // Рекурсивно инжектируем зависимости
                }
            }

            return $reflector->newInstanceArgs($dependencies);
        }

        return $reflector->newInstance();
    }
}
