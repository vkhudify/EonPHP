<?php
namespace Core;

class Logger
{
    public function log($message): void
    {
        echo "Log: " . $message . PHP_EOL;
    }
}