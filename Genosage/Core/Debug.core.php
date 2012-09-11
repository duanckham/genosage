<?php
class Debug extends Core
{	
	public function debug_start($app, $action)
	{	
		global $debug_info;
		# LOG START TIME
		$debug_info['time_start'] = $this->log_time();
		# APP AND ACTION
		$debug_info['app'] = $app;
		$debug_info['action'] = $action;
		# INIT CACHE TIMES
		$debug_info['times_cache_r'] = 0;
		$debug_info['times_cache_w'] = 0;
		# INIT DB TIMES
		$debug_info['times_db_r'] = 0;
		$debug_info['times_db_w'] = 0;
		# CACHE AND DB LOG
		$debug_info['cache_log'] = array();
		$debug_info['db_log'] = array();
	}
	
	public function debug_end()
	{
		global $debug_info;
		# LOG END TIME
		$debug_info['time_end'] = $this->log_time();
		# LOG MEMORY TIME
		$debug_info['mem_cost'] = $this->log_mem();	
	}
	
	public function debug_log($type, $log=NULL)
	{
		global $debug_info;
		switch ($type)
		{
			case 'CACHE:READ':
				$debug_info['times_cache_r']++;
				break;
			case 'CACHE:WRITE':
				$debug_info['times_cache_w']++;
				break;
			case 'DB:READ':
				$debug_info['times_db_r']++;
				break;
			case 'DB:WRITE':
				$debug_info['times_db_w']++;
				break;
			case 'CACHE':
				$debug_info['cache_log'][] = $log;
				break;
			case 'DB':
				$debug_info['db_log'][] = $log;
				break;	
			default:
				break;		
		}
	}
	
	public function debug_output()
	{
		global $debug_info;
		print_r('<script type="text/javascript">');
		print_r('console.log("Genosage Debug Info");');
		print_r('console.log("Version: '.$this->con('CORE.UPDATE_TIME').'");');
		print_r('console.log("Action: '.$debug_info['app'].'::'.$debug_info['action'].'");');
		print_r('console.log("Time cost: '.number_format($debug_info['time_end'] - $debug_info['time_start'], 8).' S");');
		print_r('console.log("Memory cost: '.$debug_info['mem_cost'].' KB");');
		print_r('console.log("Db read: '.$debug_info['times_db_r'].' Db Write: '.$debug_info['times_db_w'].'");');
		print_r('console.log("Cache read: '.$debug_info['times_cache_r'].' Cache Write: '.$debug_info['times_cache_w'].'");');
		print_r('console.log("Db query info: '.count($debug_info['db_log']).' Item(s)");');
		for ($i=0; $i<count($debug_info['db_log']); $i++)
		{
			print_r('console.log("['.($i+1).'] '.$debug_info['db_log'][$i].'");');
		}
		print_r('console.log("Cache info: '.count($debug_info['cache_log']).' Item(s)");');
		for ($i=0; $i<count($debug_info['cache_log']); $i++)
		{
			print_r('console.log("['.($i+1).'] '.$debug_info['cache_log'][$i].'");');
		}
		print_r('</script>');
	}
	
	private function log_time()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
	
	private function log_mem()
	{
		return number_format(memory_get_usage()/1024, 2);
	}
}
?>