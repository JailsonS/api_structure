<?php
namespace Models;

use Core\HelperModel;
use Models\Jwt;
use Models\Photos;

class Users extends HelperModel {

	private $id_user;

	public function create($name, $email, $password)
	{
		if(!$this->emailExists($email)){

			$hash = password_hash($password, PASSWORD_DEFAULT);

			$sql = $this->conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
			$sql->execute([$name, $email, $hash]);

			$this->id_user = $this->conn->lastInsertId();

			return true;
		} else {	
			return false;
		}
	}
	
	public function checkCredentials($email, $password)
	{	
		$sql = $this->conn->prepare("SELECT id, password FROM users WHERE email = ?");
		$sql->execute([$email]);

		if($sql->rowCount() > 0) {
			$data = $sql->fetch();
			
			if(password_verify($password, $data['password'])) {
				$this->id_user = $data['id'];
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}

	public function getId()
	{
		return $this->id_user;
	}

	public function getInfo($id)
	{
		$data = [];

		$sql = "SELECT id, name, email, avatar FROM users WHERE id = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id]);

		if($sql->rowCount() > 0) {
			$data = $sql->fetch(\PDO::FETCH_ASSOC); // PEGA SÓ AS INFORMAÇÕES BÁSICAS

			if(!empty($data['avatar'])) {
				$data['avatar'] = BASE_URL.'media/avatar/'.$data['avatar'];
			} else {
				$data['avatar'] = BASE_URL.'media/avatar/default.jpg';
			}

			$photos = new Photos();

			$data['following'] = $this->getFollowingCount($id);
			$data['followers'] = $this->getFollowersCount($id);
			$data['photos_count'] = $photos->getPhotosCount($id);

		}
		return $data;
	}

	public function getFeed($offset = 0, $per_page = 10){

		// PEGAR OS SEGUIDORES
		// FAZER LISTA DAS ÚLTIMAS FOTOS DESSES SEGUIDORES

		$photos = new Photos();

		$followingUsers = $this->getFollowing($this->getId());
		
		return $photos->getFeedCollection($followingUsers, $offset, $per_page);

	}

	public function getFollowing($id_user) 
	{
		$data = [];

		$sql = "SELECT id_user_passive FROM users_following WHERE id_user_active = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);

		if($sql->rowCount() > 0) {
			$array = $sql->fetchAll();

			foreach($array as $item) {
				$data[] = intval( $item['id_user_passive'] );
			}
		}

		return $data;
	}

	public function getFollowingCount($id_user)
	{
		$sql = "SELECT count(*) AS c FROM users_following WHERE id_user_active = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);

		$data = $sql->fetch();
		return $data['c'];
	}

	public function getFollowersCount($id_user)
	{
		$sql = "SELECT count(*) AS c FROM users_following WHERE id_user_passive = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);

		$data = $sql->fetch();
		return $data['c'];
	}

	public function editInfo($id, $data)
	{
		// SÓ PODE EDITAR AS SUAS PRÓPRIAS INFORMAÇÕES
		if($id === $this->getId()){
			$toChange = [];
			// INICIA AS VALIDAÇÕES DAS INFORMAÇÕES SETADAS PELO USUÁRIO
			if(!empty($data['name'])){
				$toChange['name'] = $data['name'];
			}

			if(!empty($data['email'])) {
				if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
					if(!$this->emailExists($data['email'])){
						$toChange['email'] = $data['email'];
					} else {
						return 'EMAIL JA EXISTENTE';
					}					
				} else {
					return 'EMAIL INVALIDO';
				}
				
			} else {
				return 'PREENCHA TODOS OS DADOS';
			}
			
			if(!empty($data['password'])){
				$hash = password_hash($data['password'], PASSWORD_DEFAULT);
				$toChange['password'] = $hash;
			}

			if(count($toChange) > 0) {

				$fields = [];
				$values = [];

				foreach($toChange as $key => $value) {
					$fields[] = $key.' = ?';
					$values[] = $value;
				}

				array_push($values, $id);

				$sql = "UPDATE users SET ".implode(', ', $fields)." WHERE id = ?";
				$sql = $this->conn->prepare($sql);
				$sql->execute($values);

			} else {
				return 'PREENCHA OS DADOS CORRETAMENTE';
			}

		} else {
			return 'NAO E PERMITIDO EDITAR OUTRO USUARIO';
		}
	}

	public function delete($id) 
	{	
		if($id === $this->getId()){

			$photos = new Photos();
			$photos->deleteAll($id);

			$sql = "DELETE FROM users_following WHERE id_user_active = ? OR id_user_passive = ?";
			$sql = $this->conn->prepare($sql);
			$sql->execute([$id, $id]);

			$sql = "DELETE FROM users WHERE id = ?";
			$sql = $this->conn->prepare($sql);
			$sql->execute([$id]);

			return '';

		} else {
			return 'NAO E PERMITIDO EXCLUIR OUTRO USUARIO';
		}
	}

	public function createJwt()
	{
		$jwt = new Jwt();
		return $jwt->create(['id_user' => $this->id_user]);
	}

	public function validateJwt($token)
	{
		$jwt = new Jwt();
		$status = $jwt->validate($token); // PEGA O TOKEN, VALIDA EM 3 PARTES E RETORNA O VALOR ORIGINAL
		
		if(isset($status->id_user)){
			$this->id_user = $status->id_user;
			return true;
		} else {
			return false;
		}
	}

	private function emailExists($email) 
	{
		$sql = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
		$sql->execute([$email]);

		if($sql->rowCount() > 0){
			return true;
		} else {
			return false;
		}
	}

}