<?php

global $config;
global $conn;

define('BASE_URL', 'http://localhost/projects/devstagram_api/');

$config = [
	'jwt_secret_key' => 'aAbc123'
];

try {
	$conn = new PDO('mysql:dbname=devsbook;host=localhost','root','');
} catch(PDOException $e) {
	echo "ERRO: ".$e->getMessage();
}