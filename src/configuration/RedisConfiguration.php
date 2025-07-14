<?php

namespace src\configuration;

class RedisConfiguration
{
    public string $databaseHost;
    public int $databasePort;
    public ?string $password;
    public int $database;

    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        ?string $password = null,
        int $database = 0
    ) {
        $this->databaseHost = $host;
        $this->databasePort = $port;
        $this->password = $password;
        $this->database = $database;
    }
} 

