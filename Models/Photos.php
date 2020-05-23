<?php
namespace Models;

use Core\HelperModel;

class Photos extends HelperModel
{

	public function getFeedCollection($ids, $offset, $per_page) 
	{
		$data = [];

		if(count($ids) > 0) {

			$id_list = implode(',', $ids);

			$sql = "SELECT * FROM photos WHERE id_user IN ($id_list) ORDER BY id DESC LIMIT $offset, $per_page";
			$sql= $this->conn->prepare($sql);
			$sql->execute();

			if($sql->rowCount() > 0) {

				$data = $sql->fetchAll(\PDO::FETCH_ASSOC);
				
			}

		}
		return $data;
	}

	public function getPhotosCount($id_user)
	{
		$sql = "SELECT count(*) AS c FROM photos WHERE id_user = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);

		$data = $sql->fetch();
		return $data['c'];
	}

	public function deleteAll($id_user)
	{
		$sql = "DELETE FROM photos WHERE id_user = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);

		$sql = "DELETE FROM photos_comments WHERE id_user = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);

		$sql = "DELETE FROM photos_likes WHERE id_user = ?";
		$sql = $this->conn->prepare($sql);
		$sql->execute([$id_user]);
	}
}