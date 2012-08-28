<?php
class Json extends Core
{	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('JSON');
	}

	public function encode($data)
	{
		return $this->format(json_encode($data));
	}
	
	public function decode($json, $isload=FALSE)
	{
		if ($isload)
		{
			$json = file_get_contents($json);
		}

		return json_decode($json, TRUE);		
	}

	public function format($json)
	{
		$count = 0; 
		$result = ''; 
		$quote = FALSE; 
		$ignore = FALSE; 
		$tab = '    '; 
		$newline = "\n"; 

		for($i=0; $i<strlen($json); $i++) { 
			$char = $json[$i]; 
			
			if ($ignore) 
			{ 
				$result .= $char; 
				$ignore = FALSE; 
			}
			else
			{
				switch($char) 
				{ 
					case '{': 
						$count++; 
						$result .= $char.$newline.str_repeat($tab, $count); 
						break; 
					case '}': 
						$count--; 
						$result = trim($result).$newline.str_repeat($tab, $count).$char; 
						break; 
					case ',': 
						$result .= $char.$newline.str_repeat($tab, $count); 
						break; 
					case '"': 
						$quote = !$quote; 
						$result .= $char; 
						break; 
					case '\\': 
						if ($quote) 
						{
							$ignore = TRUE; 
						}
						$result .= $char; 
						break; 
					default: 
						$result .= $char; 
				} 
			} 
		} 

		return $result;
	} 
}
?>