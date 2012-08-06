<?php
/**
 * Управялет конфигурацией социального апи
 * @author suver
 *
 */
class SocialApi_Config {
	
	static protected $_config = array ();
	
	public function __construct ()
	{
		
	}
	
	/**
	 * Возвращает текущий конфиг
	 */
	static public function get ()
	{
		return self::$_config;
	}
	
	/**
	 * Разберает конфиг и делает его пригодным для работы SocialApi
	 * @param unknown_type $config
	 */
	static public function parse ( $config )
	{
		$SocialApi_Config = new SocialApi_Config;
		if (is_string ($config))
		{
			$SocialApi_Config->loadConfig ( $config );
		}
		else {
			$SocialApi_Config->loadArray ( $config );
		}
	}
	
	
	/**
	 * Загружает конфиг из массива
	 * @param array $config
	 * @throws SocialApi_Exception
	 * @return null
	 */
	public function loadArray ( $config )
	{
		if ( is_array ( $config ) )
		{
			self::$_config = $config;
		}
		else {
			throw new SocialApi_Exception ( "This parameter is not an array" );
		}
	}
	
	/**
	 * Загружает конфиг из файда
	 * @param string $config
	 * @throws SocialApi_Exception
	 * @return null
	 */
	public function loadConfig ( $config )
	{
		if ( file_exists ( $config ) )
		{
			$config = include_once ($config);
			$this->loadArray ( $config );
		}
		else if ( file_exists ( dirname (__FILE__) . "/" . $config ) )
		{
			$config = include_once (dirname (__FILE__) . "/" . $config);
			$this->loadArray ( $config );
		}
		else {
			throw new SocialApi_Exception ( "Configuration settings file {$config} is not found in the specified path" );
		}
	}
	
}