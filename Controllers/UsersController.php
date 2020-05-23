<?php
namespace Controllers;

use Core\HelperController;
use Models\Users;

class UsersController extends HelperController
{
	public function index()	
	{	
	}

	public function login() 
	{
		$response = ['error'=>''];

		$method = $this->getMethod();
		$data = $this->getRequestData();

		if($method == 'POST') {
			
			if(!empty($data['email']) && !empty($data['password'])) {
				
				$users = new Users();
				
				if($users->checkCredentials($data['email'], $data['password'])) {
					$response['jwt'] = $users->createJwt();
				} else {
					$response['error'] = 'ACESSO NEGADO';
				}

			} else {
				$response['error'] = 'E-MAIL OU SENHA NAO PREENCHIDO';
			}

		} else {
			$response['error'] = 'METODO DE REQUISICAO INCORRETO!';
		}
		
		$this->returnJson($response);
	}

	public function new_record() 
	{

		$response = ['error'=>''];


		$method = $this->getMethod();
		$data = $this->getRequestData();

		if($method == 'POST'){
			if(!empty($data['name']) && !empty($data['email']) && !empty($data['password']) ) {

				if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

					$users = new Users();
					if($users->create($data['name'], $data['email'], $data['password'])) {
						$response['jwt'] = $users->createJwt();
					} else {
						$response['error'] = 'EMAIL JA CADASTRADO';
					}
				} else {
					$response['error'] = 'EMAIL INVALIDO';
				}
			} else {
				$response['error'] = 'DADO NAO PREENCHIDO';
			}
		} else {
			$response['error'] = 'METODO DE REQUISICAO INCORRETO';
		}

		$this->returnJson($response);
	}

	public function view($id)
	{
		$response = [
			'error'=>'',
			'logged'=>false,
			'is_me'=>false,
		];

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$response['logged'] = true;
			if($id == $users->getId()) {
				$response['is_me'] = true;
			}

			switch ($method) {
				case 'GET':	
					$response['data'] = $users->getInfo($id);
					if(count($response['data']) === 0) {
						$response['error'] = 'Usuário não existe';
					}

					break;
				case 'PUT':
					$response['error'] = $users->editInfo($id, $data);
					break;
				case 'DELETE':
					$response['data'] = $users->delete($id);
					break;
				default:
					$response['error'] = 'METODO: '.$method.' NAO DISPONIVEL';
					break;
			}

		} else {
			$response = ['error'=>'ACESSO NEGADO'];
		}

		$this->returnJson($response);
	}

	public function feed()
	{
		$response = [
			'error'=>'',
			'logged'=>false
		];

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
			
			$response['logged'] = true;

			if($method == 'GET') {

				$offset = 0;
				if(!empty($data['offset'])) {
					$offset = intval( $data['offset'] );
				}

				$per_page = 10;
				if(!empty($data['per_page'])) {
					$per_page = intval( $data['per_page'] );
				}

				$response['response'] = $users->getFeed($offset, $per_page);

			} else {
				$response['error'] = 'METODO: '.$method.' NAO DISPONIVEL';
			}
		}

		$this->returnJson($response);
	}

}