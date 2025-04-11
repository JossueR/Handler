<?php
namespace HandlerCore\Tests;

use Dotenv\Dotenv;
use HandlerCore\models\SimpleDAO;
use PHPUnit\Framework\TestCase;





class BaseTestCase extends TestCase
{


    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        SimpleDAO::connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    }
}