<?php
class App extends Core
{
	#
	# INIT
	#
	public function __construct($name)
	{
		parent::__construct('APP');
	}

	public function __get($property)
	{
		switch ($property)
		{
			case 'm':
				// PHP 5.3+
				$this->$property = $this->m(substr(get_called_class(), 3));
				break;
			case 'tpl':
				$this->$property = $this->core('Tpl');
				break;
			case 'auth':
				$this->$property = $this->core('Auth');
				break;
			case 'upload':
				$this->$property = $this->core('Upload');
				break;
			case 'page':
				$this->$property = FALSE;
				break;
			default:
				$this->$property = FALSE;
				break;
		}

		return $this->$property;
	}

	# TPL > ASSIGN
	public function assign($var, $value)
	{
		return $this->tpl->assign($var, $value);
	}

	# TPL > DISPLAY
	public function display($tpl_file, $real_time=FALSE)
	{
		return $this->tpl->display($tpl_file, $real_time);
	}

	# PAGE > PAGE_CONFIG
	public function page_config($key, $value)
	{
		if (!$this->page)
		{
			$this->page = $this->core('Page');
		}

		$this->page->page_config($key, $value);	
	}

	# PAGE > CREATE
	public function page($data_count)
	{
		return $this->page->create($data_count);
	}

	# UPLOAD
	public function upload($field = FALSE)
	{
		return $this->upload->upload($field);
	}
}
?>