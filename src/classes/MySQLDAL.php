<?php

/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/11/2017
 * Time: 3:31 PM
 */
class MySQLDAL {
	private $pdo;

	public function __construct(PDO $pdo){
		$this->pdo = $pdo;
	}

	public function fetchByCRN($crn){
		try{
			$q = $this->pdo->prepare("SELECT * FROM `" . DB_DATABASE_TABLE . "` WHERE `" . COLUMNS_CRN . "` = :crn");
			$q->bindValue(":crn", $crn, PDO::PARAM_INT);
			$q->execute();

			return $q->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			return null;
		}
	}

	public function fetchBySubjAndNumber($num, $subj){
		try{
			$q = $this->pdo->prepare("SELECT * FROM `" . DB_DATABASE_TABLE . "` WHERE `" . COLUMNS_COURSE_NUM . "` = :num AND `"
				. COLUMNS_FOS . "` = :subj");
			$q->bindValue(":num", $num, PDO::PARAM_INT);
			$q->bindValue(":subj", $subj, PDO::PARAM_STR);
			$q->execute();

			return $q->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			return null;
		}
	}
}
