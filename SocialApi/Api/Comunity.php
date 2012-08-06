<?php
/**
 * Класс родитель для классов унаследованных от него. 
 * Подобныая цепочка классов нужна для создания классов авторизации посредствам протокола oAuth
 * @author suver
 *
 */
 class SocialApi_Api_Comunity extends SocialApi_Api_Abstract {
	
 	static protected $objectArray = array ();
 	protected $authObject = null;
 	
	public function __construct () 
	{
		
	}

	/**
	 * Передает в объект объект авторизации в социальной сети или комюнити
	 * @param unknown_type $object
	 */
	protected function setAuthObject ( $object )
	{
		$this->authObject = $object;
	}
	
	public function __get ( $name )
	{
		if (empty (self::$objectArray[$name]))
		{
			$class = "SocialApi_Api_Comunity_{$name}";
			$object = new $class;
			$object->setAuthObject ( SocialApi::app ()->oAuth->{$name} );
			self::$objectArray[$name] = $object;
			return self::$objectArray[$name];
		}
		else
		{
			return self::$objectArray[$name];
		}
	}
	
}