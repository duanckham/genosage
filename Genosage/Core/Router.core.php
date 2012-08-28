<?php
#
#
#
class Router extends Core
{	
	private $current_rule;

	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('ROUTER');
	}

	public function analyze()
	{
		$router_rules = $this->con('ROUTER');

		$node = $this->v('u.1');
		$rule = $this->v('u.2');

		if ($node)
		{
			if (array_key_exists($node, $router_rules))
			{
				if (empty($rule) OR !array_key_exists($rule, $router_rules[$node]))
				{				
					$this->current_rule = strtolower($router_rules[$node]['*']);
					$rules = explode('/', $this->current_rule);
					$this->_dispatcher['app'] = ucfirst($rules[0]);
					$this->_dispatcher['method'] = $rules[1];
					$this->_dispatcher['url'] = __SITE__.'/?/'.$node;
					
					if (count($rules) > 1)
					{			
						for($i=2; $i<count($rules); $i++)
						{
							$this->_dispatcher['parameter'][$rules[$i]] = $this->v('u.'.($i)) ? $this->v('u.'.($i)) : 0;
							$this->_dispatcher['url'] .= '/:'.$rules[$i];
						}
					}
				}
				else
				{					
					$this->current_rule = strtolower($router_rules[$node][$rule]);
					$rules = explode('/', $this->current_rule);

					$this->_dispatcher['app'] = ucfirst($rules[0]);
					$this->_dispatcher['method'] = $rules[1];
					$this->_dispatcher['url'] = __SITE__.'/?/'.$node.'/'.$rule;
		
					if (count($rules) > 2)
					{
						for($i=2; $i<count($rules); $i++)
						{
							$this->_dispatcher['parameter'][$rules[$i]] = $this->v('u.'.($i+1)) ? $this->v('u.'.($i+1)) : 0;
							$this->_dispatcher['url'] .= '/:'.$rules[$i];
						}
					}
				}
			}	
			else
			{
				$this->error(404);
			}
		}
		else
		{
			# NULL NODE
			$this->current_rule = strtolower($router_rules['*']['*']);
			$rules = explode('/', $this->current_rule);
			$this->_dispatcher['app'] = ucfirst($rules[0]);
			$this->_dispatcher['method'] = $rules[1];
			$this->_dispatcher['url'] = __SITE__;
		}

		return $this->_dispatcher;
	}
}
?>