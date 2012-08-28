<?php
class Cache extends Core
{
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('CACHE');

		# CHECK CACHE DIR
		$cache_dir = __CACHE__.'/'.date('Ymd');
		if (is_dir($cache_dir))
		{
			if (!is_readable($cache_dir) && !is_writable($cache_dir))
			{
				$this->error('CACHE_DIR_ACCESS_DENIED');
			}
		}
		else
		{
			mkdir($cache_dir, 0777);
		}
	}

	# CHECK CACHE FILE
	public function cache_check($str, $type)
	{
		$cache_file = __CACHE__.'/'.date('Ymd').'/'.$this->hash(basename($str)).'.cache';
		switch ($type)
		{
			case 'tpl':
				if(file_exists($cache_file) && filemtime($cache_file) > filemtime(__V__.'/'.$str))
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}	
				break;
			case 'db':
				if(file_exists($cache_file) && (strtotime('now')-filemtime($cache_file)) < $this->con('CORE.CACHE_SYN_TIME'))
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}	
				break;
			default:
				return FALSE;
				break;
		}	
	}
	
	# GET CACHE FILE PATH
	public function cache_get($str)
	{
		$this->debug('CACHE:READ');
		$this->debug('CACHE', 'R '.$this->hash(basename($str)).'.cache');
		
		$feature = substr($str, 0, 6);
		if (in_array(strtolower($feature), array('select', 'update', 'insert', 'delete', 'fields'), true))
		{		
			return unserialize(file_get_contents(__CACHE__.'/'.date('Ymd').'/'.$this->hash(basename($str)).'.cache'));
		}
		else
		{
			return __CACHE__.'/'.date('Ymd').'/'.$this->hash(basename($str)).'.cache';
		}		
	}
	
	# WRITE CACHE FILE
	public function cache_save($str, $content, $type)
	{
		# CALCULATE HASH
		switch ($type)
		{
			case 'tpl':
				$hash = $this->hash(basename($str));
				$ext = 'cache';
				break;
			case 'db':
				$hash = $this->hash($str);
				$content = serialize($content);
				$ext = 'cache';
				break;
			default:
				break;
		}
		
		# GET FILE NAME
		$cache_file = __CACHE__.'/'.date('Ymd').'/'.$hash.'.'.$ext;
		# WRITE CACHE
		file_put_contents($cache_file, $content);
		# DEBUG
		$this->debug('CACHE:WRITE');
		$this->debug('CACHE', 'W '.$hash.'.'.$ext);
	}
}
?>