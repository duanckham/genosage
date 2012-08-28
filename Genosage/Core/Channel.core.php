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
			if (!in_array(__CLIENT__, explode(',', $this->con('CORE.DEBUG_IP'), true)))
			{
				$this->error("CLIENT_IP_CAN_NOT_DEBUG:".__CLIENT__);
			}
			else
			{
				$debug = TRUE;
			}
		}
		else
		{
			$debug = FALSE;
		}

		# DEBUG
		if ($debug)
		{
			$this->debug()->debug_start($this->_dispatcher['app'], $this->_dispatcher['method']);				
		}
		
		# IF AUTH IS ON
		if ($this->con('AUTH.AUTH_ON'))
		{
			$auth = $this->core('Auth');
			if (!$auth->auth_check($this->_dispatcher['app'], $this->_dispatcher['method']))
			{
				$this->redirect(__SITE__.'/?/'.$this->con('AUTH.AUTH_LOGIN'));
				return;
			}
		}
			
		# CREATE APP OBJECT
		$action = $this->c($this->_dispatcher['app']);
				
		# CALL APP
		call_user_func(array($action, $this->_dispatcher['method']));
			
		# OUTPUT DEBUG INFO		
		if ($debug)
		{			
			$this->debug()->debug_end();
			$this->debug()->debug_output();			
		}
	}
}
?>