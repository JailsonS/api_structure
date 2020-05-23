<?php
namespace Core;

class HelperModel {

	protected $conn;

	public function __construct()
	{
		global $conn;
		$this->conn = $conn;
	}

}