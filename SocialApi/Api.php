<?php
/**
 * Класс родитель для классов унаследованных от него. 
 * Подобныая цепочка классов нужна для создания классов авторизации посредствам протокола oAuth
 * @author suver
 *
 */
 class SocialApi_Api extends SocialApi {
	
 	static protected $objectArray = array ();
 	
	public function __construct () 
	{
		
	}

	public function __get ( $name )
	{
		if (empty (self::$objectArray[$name]))
		{
			$class = "SocialApi_Api_{$name}";
			$object = new $class;
			self::$objectArray[$name] = $object;
			return self::$objectArray[$name];
		}
		else 
		{
			return self::$objectArray[$name];
		}
	}

	
}