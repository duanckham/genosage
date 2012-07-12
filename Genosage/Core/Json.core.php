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

	public function encode($data, $info=NULL)
	{
		$_data['app'] = 'genosage';
		$_data['data'] = $data;
		
		if (!empty($info) AND is_array($info))
		{
			foreach ($info as $key=>$value)
			{
				$_data[$key] = $value;
			}
		}
		
		return json_encode($_data);
	}
	
	public function decode($json, $isload=FALSE)
	{
		if ($isload)
		{
			$json = file_get_contents($json);
		}

		return json_decode($json, TRUE);		
	}
}
?>