<?php

class HomeController extends Controller {

	public function index()
	{
		$books = Highscore::all();
	}

}