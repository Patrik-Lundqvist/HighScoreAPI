<?php

class APIController extends Controller {

	private $game;
	
	///
	// Constructor
	///
	public function __construct()
	{
		parent::__construct();

		// Get the public key
		$publickey = $this->app->request()->get('key');

		// Get the game from db
		$this->game = Game::where('key', $publickey)->first();
	}

	///
	// Default page
	///
	public function index()
	{
		$app->render('games.html');
	}

	///
	// Gets the top highscores
	///
	public function getHighscores()
	{
		$results = 5;

		// Get number of results
		$resultsParameter = $this->app->request()->get('results');
		
		if(!empty($resultsParameter))
		{
			if(preg_match("/^\d+$/", $resultsParameter))
			{
				$results = $this->Clamp($resultsParameter,1,100);
			}
		}


		// Get the highscores
		$highscores = Highscore::where('game_id', $this->game->id)->orderBy('score', 'DESC')->take($results)->get(array('username', 'score'));

		$highscores = $highscores->toArray();

		// Prepare the highscore array for json encoding
		$response = array('scores' => $highscores);

		$this->response(200,$response);
	}

	///
	// Creates a new highscore post
	///
	public function postHighscore()
	{
		// Decode the posted json data
		$newHighscore = json_decode($this->app->request()->post('data'));

		
		if(empty($newHighscore->version))
		{
			// Bad request
			$this->app->halt(400,"No version number");
		}

		if(!$this->validateVersionFormat($newHighscore->version))
		{
			// Bad request
			$this->app->halt(400,"Bad version format");
		}

		//Check if the version is accepted
		if(!version_compare($newHighscore->version, $this->game->version_required ,">="))
		{
			// Not Acceptable
			$this->app->halt(406,"Old game version");
		}


		if(empty($newHighscore->username)){
		   // Not Acceptable
			$this->app->halt(406,"Name is required");
		}

		if(strlen($newHighscore->username) > 20)
		{
			// Not Acceptable
			$this->app->halt(406,"Name is too long");
		}

		if ( !preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $newHighscore->username) )
		{
			// Not Acceptable
			$this->app->halt(406,"Name can only contain letters and numbers");
		}

		if(empty($newHighscore->score)){
		   // Not Acceptable
			$this->app->halt(406,"Score is required");
		}

		if (!preg_match('/^(\d+)(\.\d+)?$/', $newHighscore->score) )
		{
			// Not Acceptable
			$this->app->halt(406,"Score must be a positive decimal");
		}



		// Create a new highscore object
		$highscore = new Highscore;

		// Set the data
		$highscore->username = $newHighscore->username;
		$highscore->score = $newHighscore->score;
		$highscore->game_id = $this->game->id;
		$highscore->ip_address = $this->app->request()->getIp();

		// Save object to database
		$highscore->save();

		$response['created'] = date('Y-m-d H:i:s');

		// 201 Created
		$this->response(201,$response);
	}

	///
	// Version control
	///
	public function getVersion()
	{
		// Decode the posted json data
		$version = $this->app->request()->get('version');
		
		if(empty($version))
		{
			// Bad request
			$this->app->halt(400,"No version number");
		}

		if(!$this->validateVersionFormat($version))
		{
			// Bad request
			$this->app->halt(400,"Bad version format");
		}

		$response['Latest'] = $this->game->version_latest;

		$response['Required'] = $this->game->version_required;

		$response['UpdateExists'] = version_compare($version, $this->game->version_latest ,"<");

		$response['UpdateRequired'] = version_compare($version, $this->game->version_required ,"<");

		$this->response(200,$response);
	}
	private function Clamp($int, $min, $max)
	{

		if($int < $min)
				$int = $min;
		if($int > $max)
				$int = $max;
		return $int;

	}
	private function validateVersionFormat($version)
	{
		return preg_match('/^(\d+)(\.\d+)?(\.\d+)?(\.\d+)?$/', $version);
	}
}