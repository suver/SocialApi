<?php
/**
 * Класс содержащий необходимый базовый функционал для создания классов авторизации через oAuth2
 * @author suver
 *
 */
abstract class SocialApi_oAuth_Abstract extends SocialApi_oAuth {
	
	protected $accessToken = null;
	protected $appId = null;
	protected $appSecret = null;
	protected $code = null;
	
	public function __set ( $name, $value )
	{
		parent::__set ( $name, $value );
	}
	
	/**
	 * Проверяем авторизован ли пользователь
	 */
	abstract public function checkAuth ();
	
	/**
	 * Метод создает URL для авторизации
	 */
	abstract public function createAuthUrl ();

	/**
	 * Создает ссылку авторизации на основе переданных параметров
	 * @param array $params
	 */
	abstract public function createAuthUrlFromParams ($params=array());
	
	/**
	 * Проверяет валидность accessToken ключа
	 * @param mixed $accessToken
	 */
	abstract public function verifyIdToken ($accessToken = null);
	
	/**
	 * Обновляет accessToken и заносит новый в память
	 * Функцию записи токена в БД для дальнейшего
	 * использования нужно реализовывать отдельно
	 */
	abstract public function refreshAccessToken ();
	
	/**
	 * Запрашивает на сервере ключь accessToken
	 */
	abstract public function requestAccessToken ();
	
	/**
	 * Загружает настроки из конфигурации
	 */
	abstract public function setConfigure ();
	
	/**
	 * Устанавливает параметр code для получения токена авторизации
	 */
	public function setCode ($code) {
		$this->code = !empty ($code) ? $code : false ;
	}
	
	
	/**
	 * Возвращает параметр code
	 */
	public function getCode () {
		return $this->code;
	}

	/**
	 * Устанавливает accessToken. в метод передается полученный от сервера авторизации accessToken
	 * @param mixed $accessToken
	 */
	public function setAccessToken ($accessToken=null)
	{
		$this->accessToken = $accessToken;
	}
	
	/**
	 * Возвращает текущий accessToken
	 */
	public function getAccessToken ()
	{
		return $this->accessToken;
	}
	
	
	/**
	 * Устанавливает AppId.
	 * @param mixed $accessToken
	 */
	public function setAppId ($appId=null)
	{
		$this->appId = $appId;
	}
	
	/**
	 * Возвращает текущий AppId
	 */
	public function getAppId ()
	{
		return $this->appId;
	}
	
	
	/**
	 * Устанавливает appSecret.
	 * @param mixed $appSecret
	 */
	public function setAppSecret ($appSecret=null)
	{
		$this->appSecret = $appSecret;
	}
	
	/**
	 * Возвращает текущий appSecret
	 */
	public function getAppSecret ()
	{
		return $this->appSecret;
	}
	
}