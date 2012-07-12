<?php
class Auth extends Core
{
	private $token_hash;
	private $user_info;
	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('AUTH');
	}

	public function auth_check($app, $action)
	{	
		# GET APP & ACTION
		$app = strtolower($app);
		$action = strtolower($action);
		
		#
		# ANYONE CAN ACCESS THIS ACTION
		#
		
		# APP - ANYONE
		if (!array_key_exists($app, $this->config['auth']))
		{
			return TRUE;
		}
		
		# ACTION - ANYONE
		if (!array_key_exists($action, $this->config['auth'][$app]))
		{
			return TRUE;
		}
		
		#
		# THE ACTION NEED USER PERMISSIONS
		#
		
		# GET USER TOKEN
		return $this->auth_get_token($app, $action);
	}
			
	public function auth_get_token($app, $action)
	{
		# READ TOKEN STRING
		$token_str = $this->v('c.'.$this->config['auth']['AUTH_TOKEN_NAME']);
		
		# NOT LOGIN
		if (!$token_str)
		{
			return FALSE;			
		}
		
		# READ TOKEN
		$token_info = explode(':', $token_str);
		
		# GET HASH
		$this->auth_token_hash((int)$token_info[0]);
		
		if ($token_info[1] == $this->token_hash)
		{
			if ($this->user_info[$this->config['auth']['AUTH_GROUP']] < $this->config['auth'][$app][$action])
			{
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	public function auth_put($id)
	{
		$this->v('c.'.$this->config['auth']['AUTH_TOKEN_NAME'], $id.':'.$this->auth_token_hash($id));
	}
	
	public function auth_token_hash($id)
	{
		# GET USER INFO
		$user_info = $this->m($this->config['auth']['AUTH_TABLE'])->read('id', $id);
		
		# SAVE USER INFO
		$this->user_info = $user_info;
		
		# PUT TOKEN
		if ($user_info)
		{
			$user_str = $id;
			$user_str .= ':'.$user_info[$this->config['auth']['AUTH_USER']];
			$user_str .= ':'.$user_info[$this->config['auth']['AUTH_PASS']];
			$user_str .= ':'.$user_info[$this->config['auth']['AUTH_GROUP']];
			$user_str .= ':'.date('Ymd');
			$user_str .= ':'.$this->auth_get_ip();
		
			$this->token_hash = $this->hash($user_str);
		}
		else
		{
			$this->token_hash = 0;
		}
		
		return $this->token_hash;
	}
		
	public function auth_get_ip()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
   		else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
   		{
   			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
   		}
		else if(!empty($_SERVER['REMOTE_ADDR'])) 
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}   			
		else
		{
			$ip = '0.0.0.0';
		}
   			
		return $ip;
	}
	
	public function auth_user($key = NULL)
	{
		# READ TOKEN STRING
		$token_str = $this->v('c.'.$this->config['auth']['AUTH_TOKEN_NAME']);
		
		# NOT LOGIN
		if (!$token_str)
		{
			return FALSE;			
		}
		
		# READ TOKEN
		$token_info = explode(':', $token_str);
		
		# GET HASH
		$this->auth_token_hash((int)$token_info[0]);
		
		if ($token_info[1] == $this->token_hash)
		{
			if (empty($key))
			{
				return $this->user_info;
			}
			else
			{
				return $this->user_info[$key];
			}
		}
		else
		{
			return FALSE;
		}
	}
}
?>