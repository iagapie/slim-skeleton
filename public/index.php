<?php

declare(strict_types=1);

use App\Infrastructure\Kernel;
use Slim\App;
use Symfony\Component\Dotenv\Dotenv;

require \dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(\dirname(__DIR__).'/.env');
$kernel = new Kernel($_SERVER['APP_NAME'], $_SERVER['APP_VERSION'], $_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$app = $kernel->getContainer()->get(App::class);
$app->run();
