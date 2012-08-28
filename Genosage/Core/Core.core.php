<?php
#
# GENOSAGE CORE
#
class Core
{	
	# VARIABLE POLL
	private $_value;

	#
	# INIT
	#
	public function __construct($from = '')
	{
		# CHECK INIT STATUS
		Init::genosage();

		# SET THE GLOBAL VARIABLE
		$this->load_define();

		# ROUTER ANALYZE	
		if (in_array($from, array('CHANNEL', 'APP', 'PAGE'), TRUE))
		{	
			$this->_dispatcher = $this->core('Router')->analyze();
	
			if (isset($this->_dispatcher['parameter']))
			{
				foreach ($this->_dispatcher['parameter'] as $key => $value) 
				{
					$this->v('v.'.$key, $value);
				}
			}			
		}
	}

	#
	# START
	#
	static function start()
	{
		# CREATE GENOSAGE
		$genosage = new Core();

		# CREATE CHANNEL
		$channel = new Channel();
		$channel->channel();
	}

	#
	# SET THE GLOBAL VARIABLE
	#
	public function load_define()
	{
		if (!defined('__SITE__'))
		{
			# __SITE__
			$http_host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
			$php_self = $_SERVER['PHP_SELF'];
			define('__SITE__', 'http://'.substr($http_host.$php_self, 0, strrpos($http_host.$php_self, '/')));

			# DATA AND TIME
			define('__DATE__', date('Y-m-d'));
			define('__TIME__', date('H:i:s'));

			# CLIENT IP
			define('__CLIENT__', $_SERVER['REMOTE_ADDR']);

			# __CORE__
			define('__CONFIG__', './Config');

			# __M__, __V__, __C__
			define('__M__', './App/M');
			define('__V__', './App/V');
			define('__C__', './App/C');

			# __CACHE__
			define('__CACHE__', './Cache');

			# __PUBLIC__
			define('__JS__', './Public/Js');
			define('__CSS__', './Public/Css');
			define('__IMG__', './Public/Img');
		}		
	}	

	#
	# READ CONFIG
	#
	public function con($config_str)
	{
		if (strpos($config_str, '.'))
		{
			$config = explode('.', $config_str);
			$config_name = strtolower($config[0]);
			$config_file = 'Con'.ucfirst($config_name).'.php';
			$config_key = strtoupper($config[1]);

			# READ CONFIG FILE
			require __CONFIG__.'/'.$config_file;

			$_config = $$config_name;
			return $_config[$config_key];
		}
		else
		{
			$config_name = strtolower($config_str);
			$config_file = 'Con'.ucfirst($config_name).'.php';
			require __CONFIG__.'/'.$config_file;
			return $$config_name;
		}	
	}

	#
	# SET VALUE
	#
	public function set($key, $value)
	{
		eval('define(\'__'.strtoupper($key).'__\', \''.$value.'\');');
	}

	#
	# DEBUG
	#
	public function debug($type=NULL, $log=NULL)
	{
		if ($this->v('u.0') <> 'debug')
		{			
			return;		
		}
		
		if (empty($type))
		{
			return $this->core('Debug');
		}
		else
		{
			return $this->core('Debug')->debug_log($type, $log);
		}
	}

	#
	# IMPORT
	#
	public function import($path, $error=TRUE)
	{
		$php_file = './'.str_replace('.', '/', $path).'.php';

		if (file_exists($php_file))
		{
			require_once($php_file);
			return TRUE;
		}
		else
		{
			if ($error)
			{
				$this->error('FILE_NOT_FOUND:'.$php_file);
			}
			else
			{
				return FALSE;
			}
		}
	}

	#
	# CORE
	#
	public function core($name)
	{	
		$core = new $name();
		return $core;
	}

	#
	# CONTROLLER
	#
	public function c($name)
	{
		# IMPORT APP FILE
		$this->import('App.C.App'.$name);
		# CREATE APP OBJECT
		$app_name = 'App'.$name;	
		$app = new $app_name($name);

		return $app;
	}

	#
	# CONTROLLER
	#
	public function m($name)
	{
		$name = ucfirst($name);
		
		# IMPORT MODEL FILE
		if ($this->import('App.M.Mod'.$name, FALSE))
		{
			# CREATE MODEL OBJECT
			$mod_name = 'Mod'.$name;
			$mod = new $mod_name();
			# INIT MODEL
			$mod->load(strtolower($name));		
		}
		else
		{
			$mod = $this->core('Mod');
			$mod->load(strtolower($name));
		}

		return $mod;
	}

	#
	# VARIABLE POLL
	#
	public function v($key, $value=NULL)
	{
		# ASSIGNMENT
		if (isset($value) AND strpos($key, '.'))
		{
			# GET KEY
			$name = explode('.', $key);
			# ANALYZE VALUE TYPE
			switch($name[0])
			{
				case 'c':
					$_COOKIE[$name[1]] = $value;
					setcookie($name[1], $value, time()+$this->con('CORE.COOKIE_LIFE_TIME'));
					break;
				case 's':
					$_SESSION[$name[1]] = $value;
					break;
				case 'v':
					$this->_value[$name[1]] = $value;
					break;
				default:
					return FALSE;
					break;
			}

			return TRUE;
		}
		
		# READ VALUE
		if (strpos($key, '.'))
		{			
			# GET KEY
			$name = explode('.', $key);
			# ANALYZE VALUE TYPE
			switch($name[0])
			{
				case 'u':
					$_URl = explode('/', $_SERVER['QUERY_STRING']);
					return array_key_exists($name[1], $_URl) ? $_URl[$name[1]] : FALSE;
					break;
				case 'p':
					return array_key_exists($name[1], $_POST) ? $_POST[$name[1]] : FALSE;
					break;
				case 'c':
					return array_key_exists($name[1], $_COOKIE) ? $_COOKIE[$name[1]] : FALSE;
					break;
				case 's':
					return array_key_exists($name[1], $_SESSION) ? $_SESSION[$name[1]] : FALSE;
					break;
				case 'v':
					if (is_array($this->_value))
					{
						return array_key_exists($name[1], $this->_value) ? $this->_value[$name[1]] : FALSE;
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
		else
		{
			$value = FALSE;
			$_URl = explode('/', $_SERVER['QUERY_STRING']);
		
			array_key_exists($key, $_URl) ? $value = $_URl[$key] : $value;
			array_key_exists($key, $_POST) ? $value = $_POST[$key] : $value;
			array_key_exists($key, $_COOKIE) ? $value = $_COOKIE[$key] : $value;
			array_key_exists($key, $_SESSION) ? $value = $_SESSION[$key] : $value;
			array_key_exists($key, $this->_value) ? $value = $this->_value[$key] : $value;

			return $value;
		}
	}
	
	#
	# VARIABLE POLL
	#
	public function j($data)
	{
		$json = $this->core('Json');
		if (is_string($data))
		{
			return $json->decode($data);
		}
		else
		{
			return $json->encode($data);
		}
	}
	
	#
	# HASH - 32 PURE DIGITAL HASH
	#
	public function hash($string)
	{
		$hash = md5(md5($string).md5($string.'Genosage'));
		$hash = preg_replace('/[a-c]/', '0', $hash);
		$hash = preg_replace('/[d-f]/', '9', $hash);
		return $hash;
	}
	
	#
	# REDIRECT
	#
	public function redirect($url)
	{
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$url);
	}
	
	#
	# ERROR
	#
	public function error($msg)
	{
		print_r('ERROR.'.$msg);
		die();
	}

	#
	# SUCCESS
	#
	public function success($msg)
	{
		print_r('SUCCESS.'.$msg);
	}
}
?>