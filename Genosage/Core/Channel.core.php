<?php
class Channel extends Core
{	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('CHANNEL');
	}

	public function channel()
	{
		# IF DEBUG IS ON
		if ($this->v('u.0') == 'debug')
		{
			$this->debug()->debug_start($this->dispatcher['app'], $this->dispatcher['method']);				
		}
		
		# IF AUTH IS ON
		if ($this->config['auth']['AUTH_ON'])
		{
			$auth = $this->core('Auth');
			if (!$auth->auth_check($this->dispatcher['app'], $this->dispatcher['method']))
			{
				$this->redirect(__SITE__.'/?/'.$this->config['auth']['AUTH_LOGIN']);
				return;
			}
		}
			
		# CREATE APP OBJECT
		$action = $this->c($this->dispatcher['app']);
				
		# CALL APP
		call_user_func(array($action, $this->dispatcher['method']));
			
		# OUTPUT DEBUG INFO		
		if ($this->v('u.0') == 'debug')
		{			
			$this->debug()->debug_end();
			$this->debug()->debug_output();			
		}
	}
}
?>