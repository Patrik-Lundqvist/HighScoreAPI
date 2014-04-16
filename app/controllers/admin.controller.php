<?php

class AdminController extends Controller {

	///
	// Constructor
	///
	public function __construct()
	{
		parent::__construct();

	}

	///
	// Default page
	///
	public function index()
	{
		$this->app->render('games.html');
	}

	///
	// Get dashboard
	///
	public function getDashboard()
	{
		$this->app->render('dashboard.html');
	}

	///
	// Get 
	///
	public function getGames()
	{
		$games = Game::get();

		$data = array('games' => $games);

		$this->app->render('games.html', $data);
	}

	///
	// Creates a new game
	///
	public function postGame()
	{
		$name = $this->app->request()->post('name');
		$latestVersion = $this->app->request()->post('latest_version');
		$requiredVersion = $this->app->request()->post('required_version');

		$errors = $this->validateNameField($name);
		$errors += $this->validateVersionField($latestVersion);
		$errors += $this->validateVersionField($requiredVersion);

		if(!empty($errors))
		{
			$this->app->flash('error', $errors[0]);
			$this->app->redirect('/admin/games');
		}


		$game = new Game();

		$game->name = $name;
		$game->version_latest = $latestVersion;
		$game->version_required = $requiredVersion;
		$game->secret = bin2hex(openssl_random_pseudo_bytes(16));
		$game->key = bin2hex(openssl_random_pseudo_bytes(16));

		$game->save();

		$this->app->redirect('/admin/games');

	}


	///
	// Get game info
	///
	public function getGame($id)
	{
		// Get the game
		$game = Game::where('id',$id)->get()->first();

		// Check if an admin user exists
		if($game == null)
		{
			$this->app->halt(404,"No game found");
		}

		// Get the number of scores
		$scoreCount = Highscore::where('game_id',$game->id)->count();

		// Get the top score
		$scoreTop = Highscore::where('game_id', $game->id)->orderBy('score', 'DESC')->first();

		$data = array('game' => $game,'scoreCount' => $scoreCount);

		// If we have a top score, set the view data
		if(!empty($scoreTop->score))
		{
			$data['scoreTop'] = $scoreTop->score;
		}

		$this->app->render('game.html',$data);
	}

	///
	// Edit a game form
	///
	public function getEditGame($id)
	{
		// Get the game
		$game = Game::where('id',$id)->get()->first();

		// Check if an admin user exists
		if($game == null)
		{
			$this->app->halt(404,"No game found");
		}

		$data = array('game' => $game);

		$this->app->render('editgame.html',$data);
	}

	///
	// Edit a game
	///
	public function postEditGame($id)
	{
		// Get the game
		$game = Game::where('id',$id)->get()->first();

		// Check if an admin user exists
		if($game == null)
		{
			$this->app->halt(404,"No game found");
		}

		$editedGame = new Game();
		$editedGame->name = $this->app->request()->post('name');
		$editedGame->version_latest = $this->app->request()->post('latest_version');
		$editedGame->version_required = $this->app->request()->post('required_version');

		$errors = $this->validateNameField($editedGame->name);
		$errors += $this->validateVersionField($editedGame->version_latest);
		$errors += $this->validateVersionField($editedGame->version_required);

		if(!empty($errors))
		{
			$this->app->flashNow('error', $errors[0]);
		}
		else
		{
			$this->app->flash('info', $game->name . " has been updated!");

			// Set the new data
			$game->name = $editedGame->name;
			$game->version_latest = $editedGame->version_latest;
			$game->version_required = $editedGame->version_required;

			// Save to database
			$game->save();

			$this->app->redirect('/admin/games');
		}

		$data = array('game' => $editedGame);

		$this->app->render('editgame.html',$data);
	}


	///
	// Delete a game
	///
	public function deleteGame($id)
	{

		// Get the game
		$game = Game::where('id',$id)->get()->first();

		// Check if an admin user exists
		if($game == null)
		{
			$this->app->halt(404,"No game found");
		}
		else
		{
			$this->app->flash('info', $game->name . " has been deleted!");

			$game->delete();

			$this->app->redirect('/admin/games');
		}

	}

	////
	// Generate new key
	///
	public function getNewKey($id)
	{
		// Get the game
		$game = Game::where('id',$id)->get()->first();

		// Check if an admin user exists
		if($game == null)
		{
			$this->app->halt(404,"No game found");
		}
		else
		{
			$game->key = bin2hex(openssl_random_pseudo_bytes(16));

			$this->app->flash('info',  "New key: " . $game->key);

			$game->save();

			$this->app->redirect('/admin/games');
		}
	}


	////
	// Generate new secret
	///
	public function getNewSecret($id)
	{
		// Get the game
		$game = Game::where('id',$id)->get()->first();

		// Check if an admin user exists
		if($game == null)
		{
			$this->app->halt(404,"No game found");
		}
		else
		{
			$game->secret = bin2hex(openssl_random_pseudo_bytes(16));

			$this->app->flash('info',  "New secret: " . $game->secret);

			$game->save();

			$this->app->redirect('/admin/games');
		}
	}



	///
	// Display login form
	///
	public function getLogin()
	{
		// Get the admin user
		$user = User::where('role',"admin")->get()->first();

		// Check if an admin user exists
		if($user == null)
		{
			$this->app->render('register.html');
		}
		else
		{
			$this->app->render('login.html');
		}

	}


	///
	// Display login form
	///
	public function postLogin()
	{
		// Get the admin user
		$user = User::where('role',"admin")->get()->first();

		// Check if an admin user exists
		if($user == null)
		{
			$this->register();
		}
		else
		{
			$this->login();
		}

	}

	private function login()
	{
		$username = $this->app->request->post('username');
		$password = $this->app->request->post('password');

		$result = $this->app->authenticator->authenticate($username, $password);

		if ($result->isValid()) {
			$this->app->redirect('/admin');
		} else {
			$messages = $result->getMessages();
			$this->app->flash('error', $messages[0]);
			$this->app->redirect('/login');

		}
	}

	private function register()
	{
		$username = $this->app->request->post('username');
		$password = $this->app->request->post('password');
		$passwordRepeat = $this->app->request->post('passwordRepeat');

		if(empty($password)){
			$errors[] = "Password is required.";
		}

		if(strlen($password) > 30)
		{
			$errors[] = "Password too long.";
		}

		if(strlen($password) < 6)
		{
			$errors[] = "Password must be at least 6 characters.";
		}

		if($password != $passwordRepeat)
		{
			$errors[] = "Passwords doesn't match.";
		}

		if(empty($username)){
			$errors[] = "Username is required.";
		}

		if(strlen($username) > 20)
		{
			$errors[] = "Username too long.";
		}

		if ( !preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $username) )
		{
			$errors[] = "Username can only contain letters and numbers.";
		}

		if(!empty($errors))
		{
			$this->app->flash('error', $errors[0]);
			$this->app->redirect('/login');
		}


		$user = new User();

		$user->username = $username;
		$user->password = password_hash($password, PASSWORD_BCRYPT);
		$user->role = "admin";

		$user->save();

		$this->login();
	}

	private function validateVersionFormat($version)
	{
		return preg_match('/^(\d+)(\.\d+)?(\.\d+)?(\.\d+)?$/', $version);
	}

	private function validateNameField($name)
	{
		$errors = array();

		if(empty($name)){
			$errors[] = "Name is required.";
		}

		if(strlen($name) > 20)
		{
			$errors[] = "Name too long.";
		}

		if ( !preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name) )
		{
			$errors[] = "Name can only contain letters and numbers.";
		}

		return $errors;
	}

	private function validateVersionField($version)
	{
		$errors = array();

		if(empty($version)){
			$errors[] = "Version number is required.";
		}

		if(strlen($version) > 20)
		{
			$errors[] = "Version number too long.";
		}

		if (!$this->validateVersionFormat($version))
		{
			$errors[] = "Version number can only contain numbers and dots (max 3)";
		}

		return $errors;
	}
}
