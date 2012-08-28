<?php
class Page extends Core
{
	private $page_config;
	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('PAGE');

		# MUST
		$this->page_config['page'] = 'page';
		# OPTIONAL
		$this->page_config['count'] = 5;
		$this->page_config['size'] = 20;
		# INTERNAL
		$this->page_config['page_count'] = 0;		
		# URL
		$this->page_config['url'] = __SITE__.'/?';
	}
	

	# CONFIG OPTION
	public function page_config($key, $value)
	{
		$this->page_config[$key] = $value;
	}
	
	# CREATE PAGE
	public function create($data_count)
	{
		# GET PAGE COUNT
		$this->page_config['page_count'] = ceil($data_count/$this->page_config['size']);
		
		# GET URL
		$this->get_url();
		
		# ANALYZE
		if ($this->page_config['page_count'] <= $this->page_config['count'])
		{
			# NOW PAGE
			$page_info['now'] = $this->v('v.'.$this->page_config['page']) ? $this->v('v.'.$this->page_config['page']) : 1;
			
			# PREV PAGE AND NEXT PAGE
			$page_info['prev'] = $this->make_url('prev');
			$page_info['next'] = $this->make_url('next');
			
			# FIRST PAGE AND LAST PAGE
			$page_info['first'] = $this->make_url(1);
			$page_info['last'] = $this->make_url($this->page_config['page_count']);
			
			# SINGLE PAGE
			for ($i=0; $i<$this->page_config['page_count']; $i++)
			{
				$page_info['page'][$i+1] = $this->make_url($i+1);
			}
			
			# LIMIT
			$page_info['offset'] = ($page_info['now']-1)*$this->page_config['size'];
			$page_info['length'] = $this->page_config['size'];
		}
		else
		{
			# NOW PAGE
			$page_info['now'] = $this->v('v.'.$this->page_config['page']) ? $this->v('v.'.$this->page_config['page']) : 1;
			
			# PREV PAGE AND NEXT PAGE
			$page_info['prev'] = $this->make_url('prev');
			$page_info['next'] = $this->make_url('next');
			
			# FIRST PAGE AND LAST PAGE
			$page_info['first'] = $this->make_url(1);
			$page_info['last'] = $this->make_url($this->page_config['page_count']);
			
			# SINGLE PAGE
			if ($page_info['now'] <= ceil($this->page_config['count']/2))
			{
				for ($i=0; $i<$this->page_config['count']; $i++)
				{
					$page_info['page'][$i+1] = $this->make_url($i+1);
				}
			}
			else if ($page_info['now'] > $this->page_config['page_count'] - ceil($this->page_config['count']/2))
			{
				for ($i=0; $i<$this->page_config['count']; $i++)
				{
					$_tmp = $i + $this->page_config['page_count'] - $this->page_config['count'];
					$page_info['page'][$_tmp+1] = $this->make_url($_tmp+1);
				}
			}
			else
			{
				for ($i=0; $i<$this->page_config['count']; $i++)
				{
					$_tmp = $i + $page_info['now'] - ceil($this->page_config['count']/2);
					$page_info['page'][$_tmp+1] = $this->make_url($_tmp+1);
				}
			}
			
			# LIMIT
			$page_info['offset'] = ($page_info['now']-1)*$this->page_config['size'];
			$page_info['length'] = $this->page_config['size'];
		}
		
		return $page_info;
	}
	
	# GET URL
	private function get_url()
	{
		# GET URL
		$this->page_config['url'] = $this->_dispatcher['url'];
	}
	
	# MAKE URL
	private function make_url($page)
	{
		# NOW PAGE
		$page_now = $this->v('v.'.$this->page_config['page']) ? $this->v('v.'.$this->page_config['page']) : 1;
			
		# MAKE URL
		switch ($page)
		{
			case 'now':
				$url = str_replace(':'.$this->page_config['page'], $page_now, $this->page_config['url']);
			break;
			case 'prev':
				if ($page_now == 1)
				{
					$url = str_replace(':'.$this->page_config['page'], 1, $this->page_config['url']);
				}
				else
				{
					$url = str_replace(':'.$this->page_config['page'], $page_now-1, $this->page_config['url']);
				}
			break;
			case 'next':
				if ($page_now == $this->page_config['page_count'])
				{
					$url = str_replace(':'.$this->page_config['page'], $this->page_config['page_count'], $this->page_config['url']);
				}
				else
				{
					$url = str_replace(':'.$this->page_config['page'], $page_now+1, $this->page_config['url']);
				}
			break;
			default:
				$url = str_replace(':'.$this->page_config['page'], $page, $this->page_config['url']);
			break;
		}

		$url_group = explode('/', $url);

		for ($i=0; $i<count($url_group); $i++)
		{
			if (strstr($url_group[$i], ':') && strpos($url_group[$i], ':') == 0)
			{
				$url = str_replace($url_group[$i], $this->v('v.'.str_replace(':', '', $url_group[$i])), $url);
			}
		}		
		return $url;
	}
}
?>