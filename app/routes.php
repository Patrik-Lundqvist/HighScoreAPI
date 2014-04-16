<?php
///
// Routes
///


// Front-page "/"
$app->get('/', function () use ($app) {
	$app->AdminController->index();
});

// POST new highscore "/highscore/new"
$app->post('/highscore/new','authenticateSignature', function () use ($app) {
	$app->APIController->postHighscore();
});

// GET top highscores "/highscore/top"
$app->get('/highscore/top','authenticateSignature', function () use ($app) {
	$app->APIController->getHighscores();
});

// GET version info "/version"
$app->get('/version','authenticateSignature', function () use ($app) {
	$app->APIController->getVersion();
});

// GET Login page form "/login"
$app->get('/login', function () use ($app) {
	$app->AdminController->getLogin();
})->name('login');

// POST Login "/login"
$app->post('/login', function () use ($app) {
	$app->AdminController->postLogin();
});

// GET Logout "/logout"
$app->get('/logout', function () use ($app) {
	$app->authenticator->logout();
	$app->redirect('/');
});

// GET Admin dashboard page "/admin"
$app->get('/admin', function () use ($app) {
	$app->AdminController->getDashboard();
});



// GET Admin games "/admin/games"
$app->get('/admin/games', function () use ($app) {
	$app->AdminController->getGames();
});

// POST new game
$app->post('/admin/games', function () use ($app) {
	$app->AdminController->postGame();
});

// GET Admin game "/admin/game/$int"
$app->get('/admin/game/:id', function ($id) use ($app) {
	$app->AdminController->getGame($id);
});

// GET Deletes a game "/admin/games/$int/delete"
$app->get('/admin/game/:id/delete', function ($id) use ($app) {
	$app->AdminController->deleteGame($id);
})->conditions(array('id' => '[0-9]{1,5}'));

// GET Edit a game "/admin/games/$int/edit"
$app->get('/admin/game/:id/edit', function ($id) use ($app) {
	$app->AdminController->getEditGame($id);
})->conditions(array('id' => '[0-9]{1,5}'));

// Post Edit a game "/admin/games/$int/edit"
$app->post('/admin/game/:id/edit', function ($id) use ($app) {
	$app->AdminController->postEditGame($id);
})->conditions(array('id' => '[0-9]{1,5}'));

// GET new key "/admin/games/$int/newkey"
$app->get('/admin/game/:id/newkey', function ($id) use ($app) {
	$app->AdminController->getNewKey($id);
})->conditions(array('id' => '[0-9]{1,5}'));

// GET new secret "/admin/games/$int/newsecret"
$app->get('/admin/game/:id/newsecret', function ($id) use ($app) {
	$app->AdminController->getNewSecret($id);
})->conditions(array('id' => '[0-9]{1,5}'));

///
// Authenticate a request (make sure the signature is valid)
///
function authenticateSignature(\Slim\Route $route) {

	$response = array();

	// Get the app instance
	$app = \Slim\Slim::getInstance();

	// Get the public key
	$publickey = $app->request()->get('key');

	// Get the signature
	$sig = $app->request()->get('sig');

	
	// Verifying Authorization Header
	if (isset($publickey) && isset($sig)) {

		// Get the private hash from the database
		$game = Game::where('key', $publickey)->first();

		if (empty($game)) {

			$app->halt(400,"Bad key");
		}

		// Get the secret hash
		$privateHash = $game->secret;

		// Get the content from the http request
		$content = urldecode($app->request()->getBody());

		// Get the app path (e.x '/highscore/new')
		$url = $app->request()->getPathInfo();

		// Generate a signature
		$computed_signature = base64_encode(hash_hmac('sha256', $url . $content . $privateHash, $privateHash, TRUE));
		
		// Make sure oassed signature matches the generated one
		if ($computed_signature != $sig) {
			
			// Error, Signature is bad
			$app->halt(403,"Bad signature");
		}

	} else {

		// Api key or signature is missing
		$app->halt(403,"Api key or signature is misssing");
	}
}

///
// Encode response to JSON and return with status code
///
function response($status,$body)
{
	$app = \Slim\Slim::getInstance();

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
	$response->body(json_encode(array($body)));
	$response->status($status);
}

$app->container->singleton('APIController', function () {
	return new APIController();
});

$app->container->singleton('AdminController', function () {
	return new AdminController();
});

