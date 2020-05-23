<?php
namespace Controllers;

use Core\HelperController;

class NotfoundController extends HelperController
{
	public function index()
	{
		$this->returnJson([]);
	}
}