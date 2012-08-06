<?php
/**
 * Класс родитель SocialApi.
 * Содержит базовый набор методов и запрещений
 * @author suver
 *
 */
abstract class SocialApi_Abstract {
	
	/**
	 * Закрываем возможность прямого изменения параметров класса
	 * @param string $name
	 * @param mixed $value
	 * @throws SocialApi_Exception
	 */
	public function __set ( $name, $value )
	{
		throw new SocialApi_Exception ( "You can not directly change the parameters of the object SocialApi" );
	}
	
}