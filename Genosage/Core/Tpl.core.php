<?php
class Tpl extends Core
{
	private $vars;
	private $parser;
	private $template;
	private $tpl_file;
	private $tpl_file_path;
	private $cache;
	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('TPL');

		$this->cache = $this->core('Cache');
	}

	# SET VALUE
	public function assign($var, $value) 
	{
		if(isset($var) && trim($var) != '') 
		{
			$this->vars[$var] = $value;
			return TRUE;
		}
	}
	
	# DISPLAY TPL VIEW
	public function display($tpl_file, $real_time=FALSE) 
	{
		$this->tpl_file = $tpl_file.'.tpl';
		$this->tpl_file_path = __V__.'/'.$tpl_file.'.tpl';
			
		if ($real_time OR !$this->cache->cache_check($this->tpl_file, 'tpl'))
		{			
			if(!file_exists($this->tpl_file_path)) 
			{
				$this->error('TPL_NOT_FOUND:'.$this->tpl_file);
			}
			# COMPILE
			$this->compile();
		}
		
		# DISPLAY
		require_once($this->cache->cache_get($this->tpl_file));
	}
	
	# READ TPL FILE
	private function read_tpl() 
	{	
		if(!is_readable($this->tpl_file_path))
		{
			$this->error('READ_TPL_ERROR:'.$this->tpl_file);
		}
		
		if(filesize($this->tpl_file_path)) 
		{
			$this->template = file_get_contents($this->tpl_file_path);
		} 
		else 
		{
			$this->error('TPL_FILE_EMPTY:'.$this->tpl_file);
		}

		return TRUE;
	}
	
	# ASSEMBLING
	private function assembling()
	{
		$include_patten_tpl = '/{\s*include "([^}]+tpl\b)"\s*}/';
									
		while (preg_match_all($include_patten_tpl, $this->template, $tpl_file))
		{			
			for ($i=0; $i<count($tpl_file[0]); $i++)
			{
				$tpl_content = file_get_contents(__V__.'/'.$tpl_file[1][$i]);					
				$this->template = str_replace($tpl_file[0][$i], $tpl_content, $this->template);
			}			
		}
	}
	
	# COMPILE
	private function compile() 
	{	
		# LOAD TPL
		$this->read_tpl();
		# ASSEMBLING
		$this->assembling();
		# PARSE
		$this->parse_var();
		$this->parse_if();
		$this->parse_common();
		$this->parse_foreach();
		$this->parse_include();
		# WRITE CACHE
		$this->cache->cache_save(basename($this->tpl_file), $this->template, 'tpl');
	}
	
	# USE:
	# {$value}
	#
	private function parse_var()
	{
		$patten = "/\{\\$([a-zA-Z0-9_]{1,})\}/";
		if(strpos($this->template, '{$') !== FALSE)
		{
			$this->template = preg_replace($patten, "<?php echo \$this->vars['$1']; ?>", $this->template);
		}
		return TRUE;
	}
	
	# USE:
	# {if tset:='a'}<p>test is a</p>{/if}
	#
	private function parse_if()
	{
		if(preg_match("/\{\s*if/", $this->template))
		{
			if(preg_match('/\{\s*\/if\s*\}/', $this->template)) 
			{
				$if_patten = "/\{\s*if\s+([^}]+)\}/";
				$ef_patten = "/\{\s*\/if\s*\}/";
				
				preg_match_all($if_patten, $this->template, $result, PREG_OFFSET_CAPTURE, 3);
				
				$if_code = array();			
				for ($i=0; $i<count($result[1]); $i++)
				{
					$if_temp = explode(":=", $result[1][$i][0]);
					$if_code[$i]['tag'] = '[IF:'.$if_temp[0].':='.$if_temp[1].']';
					$if_code[$i]['code'] = "<?php if (\$this->vars['".$if_temp[0]."'] == ".$if_temp[1].'): ?>';
				}
				
				$this->template = preg_replace($if_patten, '[IF:$1]', $this->template);
				$this->template = preg_replace($ef_patten, "<?php endif; ?>", $this->template);
				
				for ($i=0; $i<count($if_code); $i++)
				{
					$this->template = str_replace($if_code[$i]['tag'], $if_code[$i]['code'], $this->template);
				}
			} 
			else
			{
				$this->error('TPL_SYNTAX_ERROR:IF');
			}
		}
		return TRUE;
	}
	
	# USE:
	# {#}Common{#}
	#
	private function parse_common() 
	{
		$patten = "/\{#\}([^{]*)\{#\}/";
		if(strpos($this->template, '{#}') !== FALSE) 
		{
			$this->template = preg_replace($patten, "<?php /* $1 */ ?>", $this->template);
		}
		return TRUE;
	}
	
	# USE:
	# {$arr->for(key, value)}
	# <p>{@key}{@value}</p>
	# {/for}
	#
	private function parse_foreach()
	{
		if(preg_match("/\{\s*\\$[0-9a-zA-Z_]+\s*->\s*for/", $this->template)) 
		{
			if(preg_match("/{\s*\/for\s*}/", $this->template)) 
			{
				if(preg_match("/\{\s*@[\w\[\]\']+/", $this->template)) 
				{
					$k_and_v_patten = "/\{\s*@([\w\[\]\'\"]+)\s*\}/";
					$this->template = preg_replace($k_and_v_patten, "<?php echo \$$1; ?>", $this->template);
				}
				$foreach_patten = "/\{\s*\\$([0-9a-zA-Z_]+)\s*->\s*for\(\s*([0-9a-zA-Z_]+)\s*,\s*([0-9a-zA-Z_]+)\s*\)\s*\}/";
				$end_foreach_patten = "/\{\s*\/for\s*\}/";
				$this->template = preg_replace($foreach_patten, "<?php foreach(\$this->vars['$1'] as \$$2 => \$$3): ?>", $this->template);
				$this->template = preg_replace($end_foreach_patten, "<?php endforeach; ?>", $this->template);
			} 
			else
			{
				$this->error('TPL_SYNTAX_ERROR:FOR');
			}
		}
	}
	
	# USE:
	# {include "Path"} - JS, CSS, HTML
	#
	private function parse_include() 
	{
		if(preg_match("/\{\s*include \"([^}]*)\"\s*\}/", $this->template, $file)) 
		{
			if(trim($file[1]) == '')
			{
				$this->error('TPL_SYNTAX_ERROR:INCLUDE_FILE_NAME_EMPTY');
			}
			else
			{
				$include_patten_js = '/{\s*include "([^}]+js\b)"\s*}/';
				$include_patten_css = '/{\s*include "([^}]+css\b)"\s*}/';
				$include_patten_less = '/{\s*include "([^}]+less\b)"\s*}/';

				# JAVASCRIPR FILE				
				$this->template = preg_replace($include_patten_js, '<script src="'.__JS__.'/$1" type="text/javascript"></script>', $this->template);
				# CSS FILE			
				$this->template = preg_replace($include_patten_css, '<link type="text/css" rel="stylesheet" href="'.__CSS__.'/$1"/>', $this->template);
				# LESS FILE				
				$this->template = preg_replace($include_patten_less, '<link type="text/css" rel="stylesheet/less" href="'.__CSS__.'/$1"/>', $this->template);			
			}		
		}
		return TRUE;
	}
}
?>