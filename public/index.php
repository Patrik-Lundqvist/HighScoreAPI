<?php
define('ENVIRONMENT', isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'local');

require_once "../vendor/autoload.php";

require_once "../config.php";
require_once "../app/application.php";

// Controllers
require_once "../app/controller.php";
require_once "../app/controllers/home.controller.php";

// Models
require_once "../app/models/BaseModel.php";
require_once "../app/models/Highscore.php";

define('APPLICATION', 'Highscore-API');
define('VERSION', '0.0.1');

use Slim\Slim;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$app = new Slim();

/* Content Type Middleware */
$app->add(new \Slim\Middleware\ContentTypes());

$capsule = new Capsule;

$capsule->addConnection($databseSettings);

$capsule->setEventDispatcher(new Dispatcher(new Container));

// If you want to use the Eloquent ORM...
$capsule->bootEloquent();

/* DB methods accessible via Slim instance */
$capsule->setAsGlobal();

$c = new Application($app);

// Include Routes
require_once "../app/routes.php";

$app->run();