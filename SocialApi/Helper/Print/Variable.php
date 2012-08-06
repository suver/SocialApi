<?php
class SocialApi_Helper_Print_Variable {
	
	static public function dump ($variable=array ())
	{
		self::_dump ($variable);
	}
	
	private function _dump ($variable=array (),$iteration=0) 
	{
		if (is_array ($variable)) 
		{
			foreach ($variable as $k=>$v) 
			{ 
				if ($iteration <= 0) {
					echo SocialApi_Helper_Colored::out( $k, 30);
				}
				else {
					echo "[" . SocialApi_Helper_Colored::out( $k, 34) . "]";
				}
				$iteration++;
				self::_dump ( $v, $iteration );
				$iteration--;
			}
		}
		else 
		{
			echo ": " . SocialApi_Helper_Colored::out( $variable, 36);
			echo "<br>\r\n";
		}
	}
	
}