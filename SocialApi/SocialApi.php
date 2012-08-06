<?php
/**
 * Социальное АПИ
 * Набор команд для доступа к функциям популярных социальных сервисов
 * @author Suver 2012
 */

/**
 * Путь с файлами Vile Elvis
 */
define ( "SA_PATH", dirname (__FILE__) );

include_once SA_PATH."/Abstract.php";

class SocialApi extends SocialApi_Abstract {

	protected $config;
	protected $objectCollaction = array ();
	static public $SocialApi_Object;

	/**
	 * Конструктор Vile Elvis
	 * @param stdObject/array $config
	 */
	public function __construct ($config = false)
	{
		if ($config)
		{
			$this->setConfig ($config);
		}
		$this->init ();
	}
	
	/**
	 * Загружает конфиг
	 * @param unknown_type $config
	 */
	public function setConfig ( $config )
	{
		if ($config)
		{
			$this->config = SocialApi_Config::parse ($config);
		}
	}
	
	/**
	 * Используется для доступа к разрешенным параметрам объекта
	 * @param unknown_type $name
	 * @return string
	 */
	public function __get( $name )
	{
		if (isset($this->objectCollaction[$name]))
		{
			return $this->objectCollaction[$name]; 
		}
		else 
		{
			throw new SocialApi_Exception ( "{$name} not specified" );
		}
	}
	
	/**
	 * Передает класс авторизации SocalApi_oAuth
	 * @param unknown_type $oAuth
	 */
	public function init ( ) 
	{
		$this->objectCollaction['oAuth'] = new SocialApi_oAuth;
		$this->objectCollaction['Api'] = new SocialApi_Api;
		
		self::$SocialApi_Object = $this;
	}
	
	/**
	 * Реализует доступ к Апи по средствам Singelton. Однако, следует учесть что доступ не 
	 * ограничивается патерном Singlton и методы или поведение могут переопределятся из других 
	 * частей программы или АПИ. Используйте данный способ доступа для быстрого получения данных.
	 * @return SocialApi
	 */
	static public function app () 
	{
		if (empty (self::$SocialApi_Object))
		{
			throw new SocialApi_Exception( gettext ("before using it you need to initialize the "
													."object through the constructor SocialApi") );
		}
		return self::$SocialApi_Object;
	}
	
	/**
	 * Автолоадер для Vile Elvis
	 * @param string $className
	 */
	public static function autoload ($className)
	{
		if (preg_match ( "#^SocialApi_#is", $className )) {
			$_className = str_replace ( "SocialApi_", "", $className );
			$local_path = str_replace ( "_", DIRECTORY_SEPARATOR, $_className );
			// use include so that the error PHP file may appear
			if(file_exists (SA_PATH.DIRECTORY_SEPARATOR.$local_path.".php"))
			{
				include_once (SA_PATH.DIRECTORY_SEPARATOR.$local_path.".php");
			}
			else
			{
				throw new SocialApi_Exception (gettext ("Class {$className} not specified"));
			}
			return true;
		}
	}

}


spl_autoload_register(array('SocialApi','autoload'));