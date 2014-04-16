<?php
define('ENVIRONMENT', isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'local');

require_once "../vendor/autoload.php";

require_once "../config.php";
require_once "../app/application.php";
require_once "../app/acl.php";

// Controllers
require_once "../app/controller.php";
require_once "../app/controllers/api.controller.php";
require_once "../app/controllers/admin.controller.php";

// Models
require_once "../app/models/BaseModel.php";
require_once "../app/models/Game.php";
require_once "../app/models/Highscore.php";
require_once "../app/models/User.php";


define('APPLICATION', 'Highscore-API');
define('VERSION', '0.0.1');

use Slim\Slim;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

use JeremyKendall\Password\PasswordValidator;
use JeremyKendall\Slim\Auth\Adapter\Db\PdoAdapter;
use JeremyKendall\Slim\Auth\Bootstrap;

$app = new \Slim\Slim(array(
    'debug' => true,
    'view' => new \Slim\Views\Twig(),
    'templates.path' => '../templates/',
    'cookies.encrypt' => true,
    'cookies.secret_key' => $cookieSecret
));

// Slim Auth PDO instace
$db = new PDO("{$databseSettings['driver']}:host={$databseSettings['host']};dbname={$databseSettings['database']}",$databseSettings['username'],$databseSettings['password']);
$adapter = new PdoAdapter(
    $db, 
    "users", 
    "username", 
    "password", 
    new PasswordValidator()
);

$acl = new Acl();
$authBootstrap = new Bootstrap($app, $adapter, $acl);
$authBootstrap->bootstrap();

// Add the session cookie middleware *after* auth to ensure it's executed first
$app->add(new \Slim\Middleware\SessionCookie());

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