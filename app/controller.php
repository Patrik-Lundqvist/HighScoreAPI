<?php
abstract class Controller extends Application
{

	public function __construct()
	{
		parent::__construct();

	}

	public function redirect($name, $routeName = true)
	{
		$url = $routeName ? $this->app->urlFor($name) : $name;
		$this->app->redirect($url);
	}

	public function get($value = null)
	{
		return $this->app->request()->get($value);
	}

	public function post($value = null)
	{
		return $this->app->request()->post($value);
	}

	public function response($status,$body)
	{
		$response = $this->app->response();
		$response['Content-Type'] = 'application/json';
		$response->body(json_encode($body));
		$response->status($status);
	}

}