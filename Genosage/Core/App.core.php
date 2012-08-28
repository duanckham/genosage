<?php
class App extends Core
{
	# TPL
	public $tpl;
	public $page = FALSE;

	#
	# INIT
	#
	public function __construct($name)
	{
		parent::__construct('APP');

		$this->tpl = $this->core('Tpl');
		$this->m = $this->m($name);
		$this->auth = $this->core('Auth');
		$this->upload = $this->core('Upload');
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