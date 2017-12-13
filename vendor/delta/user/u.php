<?php

//namespace usr;

/**
 *
 *
 *
 * 
 */
trait user_u {

	protected $username = false;
	protected $password = false;
	protected $isLogged = false;
	protected $userProp = false;
	protected $userInfo = [];
	protected static $_USER_TABLE  = false;



/*
	if (!defined('self::$_USER_TABLE')) {
		define('self::$_USER_TABLE', $this->getFullTableName( '_users' ));
	}
*/
	



/**
 *
 *
 *
 * 
 */
protected function checkAutoLogin(){
	$this->toPlaceholder('islogged', '0' , 'px.');

	if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
		$tUsername = $_SESSION['username'];
		$tPassword = $_SESSION['password'];
		//echo 'ddd1';
	}elseif  (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
		$tUsername = $_COOKIE['username'];
		$tPassword = $_COOKIE['password'];
		//echo 'ddd2';
	}elseif (Request::fieldGET('username') && Request::fieldGET('password')) {
		$tUsername = Request::fieldGET('username');
		$tPassword = $this->saltPSWD(md5(Request::fieldGET('password')));
		//echo 'ddd3'; 
	}

	if ( ($user = $this->getUserFromDB($tUsername) ) && $user != false) {
		if  ( ($salt = $tPassword ) && $salt == $user['passw'] && $salt !== false) {
			if($user['confirm']) {
				$this->userInfo = $user;
				$this->isLogged = true;
				$this->clearCache();
				$this->setUserPlaceholder();
				$this->toPlaceholder('islogged', '1' , 'px.');
				setcookie('logged', true);
				setcookie('username', $this->userInfo['login']);
				setcookie('password', $this->userInfo['passw']);
				$_SESSION['username'] = $this->userInfo['login'];
				$_SESSION['password'] = $this->userInfo['passw'];
				return $this->isLogged;
			}
		}
	}
	$this->checkOut();
	return false;
}






/**
 *
 *
 *
 * 
 */
protected function confirmMail(){
	if (! is_null( ($code = Request::fieldGET('acceptcode')))) {
		$fields = array('confirm' => 1);  
		if ($this->db->update( $fields, self::$_USER_TABLE, 'confirmkey = "' . $code . '"  LIMIT 1' ) ) {
			if ($this->db->getAffectedRows() == 1) {
				$this->toPlaceholder('confirmMailEvent', '1' , 'px.');
				return true;
			}
		}else {
			//$this->toPlaceholder('confirmMailEvent', '0' , 'px.');
			//return false;
		}
	}
	$this->toPlaceholder('confirmMailEvent', '0' , 'px.');
	return false;
}










/**
 *
 *
 *
 * 
 */
protected function setUserPlaceholder($data = array()){
	if (is_array($data) && count($data)) {
		$this->toPlaceholders($this->data,'px.'); 
	}

	if (is_array($this->userInfo) && count($this->userInfo)) {
		$this->toPlaceholders($this->userInfo,'px.'); 
	}
}






/**
 *
 *
 *
 * 
 */
protected function generateConfirmKey(){
	return rand(10000,99999).rand(1000,3000).rand(3000,999);
}





/**
 *
 *
 *
 * 
 */
protected function addUser($usr = array() , $mailChunk = false){
	$result = [];
	//TODO: Запилить метод для проверки валидности полей
	if (! $mailChunk ) {
		$result['state'] = false;
		$result['err'] = 'Не определен шаблон письма';
	}else{
		if ($this->checkUsername($usr['login']) === false) {
			$usr['passw'] = $this->saltPSWD(md5($usr['passw'])); 
			try {
				$usr['confirmkey'] = $this->generateConfirmKey();
				if ($this->db->insert($usr, self::$_USER_TABLE )) {
					$parsedChunk = $this->parseText($this->getChunk($mailChunk) , $usr , '[+px.');
					$parsedChunk = $this->rewriteUrls($parsedChunk);
					$this->runSnippet('sendQuickEmail' , array(
						'subject' => 'Подтверждение регистрации на сайте '.$_SERVER['HTTP_HOST'] , 
						'clientMail' => $usr['email'] , 
						'emailtext' => $parsedChunk,
						'clientOnly' => true,
					) );
					$result['state'] = true;
				} 
			} catch (Exception $e) {
				$result['state'] = false;
				$result['err'] = 'Ошибка БД';
			}
		}else {
			$result['state'] = false;
			$result['err'] = 'Пользователь с таким логином уже занят';
		}
	}
	return $result;
}






/**
 *
 *
 *
 * 
 */
protected function checkOut(){
	unset( $_SESSION['username'] , $_SESSION['password'] );
	unset( $_COOKIE['username']  , $_COOKIE['password'] );
	$_SESSION['logged'] = false;
	setcookie('logged', false);
	$this->setPlaceholder('islogged', '0');
	$this->isLogged = false;
}





/**
 *
 *
 *
 * 
 */
protected function auth($login = false , $password = false){
	if ( ! ($login || $password)) return false;
	/*
	$result = $this->db->select("*", $this->getFullTableName('_users'), "login='" . $login ."'" , '' , 1 ); 
	if($this->db->getRecordCount($result)) { 
		$row = $this->db->getRow($result); 
		return $row;
	}else{ 
		return false;
	} */
}






/**
 *
 *
 *
 * 
 */
protected function getUserFromDB($login = false){
	if ( !$login || $login == '') return false;
	$result = $this->db->select("*", self::$_USER_TABLE, "login='" . $login ."'" , '' , 1 ); 
	if($this->db->getRecordCount($result)) { 
		$row = $this->db->getRow($result); 
		return $row;

	}else{ 
		return false;
	} 
}






/**
 *
 *
 *
 * 
 */
protected function checkUsername($login = false){
	if ( !$login || $login == '') return false;
	$result = $this->db->select("id_user", self::$_USER_TABLE, "login='" . $login ."'" , '' , 1 ); 
	if($this->db->getRecordCount($result)) { 
		return true;
	}else{ 
		return false;
	} 
}






/**
 *
 *
 *
 * 
 */
protected function saltPSWD($pass = false){
	if ($pass === false) return false;
	return md5(strrev($pass));
}




/**
 *
 *
 *
 * 
 */
public function islogged(){
	return $this->isLogged;
	//echo PxCabs_Request::fieldGET('dsfds');
}



}
