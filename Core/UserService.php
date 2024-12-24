<?php
namespace Core;

use Core\Logger;

class UserService
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function createUser($name)
    {
        $this->logger->log("user '$name' created.");
    }
}