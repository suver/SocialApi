<?php
define ("SE_COLORED_NORMAL", 0);
define ("SE_COLORED_BOLD", 1);
define ("SE_COLORED_UNDERLINE", 4);

class SocialApi_Helper_Colored {
	
	protected $color = array (
		30 => '#000000', // Черный
		31 => '#FF0000', // красный
		32 => '#00FF00', // Зеленый
		33 => '#CD8500', // Оранжевый
		34 => '#6CA6CD', // Синий
		35 => '#8B658B', // Фиолетовый
		36 => '#008B8B', // Бирюзовый
		37 => '#828282', // Серый
	);

	static public function out ( $string, $color, $style = VE_COLORED_NORMAL ) {
		$colored = new SocialApi_Helper_Colored;
		switch (VE_RUN_AS) {
			case VE_RUN_AS_WEB:
				$string = $colored->coloredForHtml ( $string, $color, $style );
				break;
			case VE_RUN_AS_CLI:
				$string = $colored->coloredForCli ( $string, $color, $style );
				break;
			case VE_RUN_AS_CRON:
			default:
				$string = $colored->withoutColor ( $string, $color, $style );
				break;
		}
		return $string;
	}
	
	protected function coloredForHtml ( $string, $color, $style ) {
		switch ($style) {
			case VE_COLORED_UNDERLINE:
				$style = ' style="text-decoration:underline;"';
				break;
			case VE_COLORED_BOLD:
				$style = ' style="font-weight:bold;"';
				break;
			case VE_COLORED_NORMAL:
			default:
				$style = '';
		}
		return "<font color='" . $this->_getCodeColor ($color) . "'{$style}>$string</font>";
	}
	
	
	protected function coloredForCli ( $string, $color, $style ) {
		$color = $this->_getIndexColor ($color);
		switch ($style) {
			case VE_COLORED_UNDERLINE:
				$style = '4';
				break;
			case VE_COLORED_BOLD:
				$style = '1';
				break;
			case VE_COLORED_NORMAL:
			default:
				$style = '0';
		}
		return "\033[{$style};{$color}m{$string}\033[00m";
	}
	
	
	protected function withoutColor ( $string, $color, $style ) {
		return $string;
	}
	
	protected function _getCodeColor ( $num ) {
		return isset ($this->color[$num]) ? $this->color[$num] : $this->color[30];
	}
	
	
	protected function _getIndexColor ( $num ) {
		return isset ($this->color[$num]) ? $num : 30;
	}
	
}