<?php

define ("SE_METHOD_POST", 	'POST');
define ("SE_METHOD_PUT", 	'PUT' );
define ("SE_METHOD_GET", 	'GET' );
define ("SE_METHOD_DELETE", 'DELETE');

/**
 * Класс родитель для классов доступа к АПИ различных сервисов 
 * @author suver
 *
 */
abstract class SocialApi_Api_Comunity_Abstract extends SocialApi_Api_Comunity {
	
	protected $apiKey = null;
	
	/**
	 * Возвращает информацию о залогиненом пользователе
	 */
	abstract public function me ();
	
	/**
	 * Возвращает информацию о вызываемом объекте
	 */
	abstract public function get ($objectId);
	
	/**
	 * Возвращает информацию о вызываемом объекте
	 */
	abstract public function feed ($objectId='me');
	
	/**
	 * Совершает поиск по социальной сети или комюнити
	 */
	abstract public function search ($search);
	
	/**
	 * Реализует доступ к функциям АПИ. Метод отправки на основании параметра $sendMethod 
	 */
	abstract public function api ( $method=null, $params=array (), $sendMethod=SE_METHOD_GET );
	
	/**
	 * Реализует доступ к функциям АПИ. Метод отправки POST
	 */
	public function sendPOST ( $method=null, $params=array () )
	{
		return $this->api ( $method, $params, SE_METHOD_POST );
	}
	
	/**
	 * Реализует доступ к функциям АПИ. Метод отправки GET
	 */
	public function sendGET ( $method=null, $params=array () )
	{
		return $this->api ( $method, $params, SE_METHOD_GET );
	}
	
	/**
	 * Реализует доступ к функциям АПИ. Метод отправки PUT
	 */
	public function sendPUT ( $method=null, $params=array () )
	{
		return $this->api ( $method, $params, SE_METHOD_PUT );
	}
	
	/**
	 * Реализует доступ к функциям АПИ. Метод отправки DELETE
	 */
	public function sendDELETE ( $method=null, $params=array () )
	{
		return $this->api ( $method, $params, SE_METHOD_DELETE );
	}
	
	
	/**
	 * Устанавливает параметр api_key
	 */
	public function setApiKey ($apiKey) {
		$this->apiKey = !empty ($apiKey) ? $apiKey : false ;
	}
	
	
	/**
	 * Возвращает параметр api_key
	 */
	public function getApiKey () {
		return $this->apiKey;
	}
}