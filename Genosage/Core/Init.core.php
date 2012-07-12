<?php
class Init extends Core
{
	public function genosage()
	{
		# CHECK THE INIT STATUS
		if (is_dir('./App') AND is_dir('./Config') AND is_dir('./Cache'))
		{
			return;
		}

		# > FOLDER
		$sys_folders = array('App', 'App/M', 'App/V', 'App/C', 'Cache', 'Config', 'Public', 'Public/Js', 'Public/Css', 'Public/Img', 'Upload');

		for ($i=0; $i<count($sys_folders); $i++)
		{
			if (!is_dir('./'.$sys_folders[$i]))
			{
				mkdir('./'.$sys_folders[$i], 0777);
			}
		}
		
		# > CONFIG FILE
		$sys_config_files['core'] = gzinflate(base64_decode('4+VSSc7PS8tMj0/OL0pVsFVILCpKrNTQtObl4kWRilYPDXBxDHGND/H0dVWPBapUMjIwNDIwNzRSskZX6uzv7+3pGu/j6Yak3sLMxMAAU6mjs4drfHCkH0KhMUgVAA=='));
		$sys_config_files['router'] = gzinflate(base64_decode('4+VSSc7PS8tMjy/KLy1JLVKwVUgsKkqs1NC05uXiRZOMVtdSj4Wr4OXiBPIVbO0U1D3zUlIr9MGkOi8XSCsA'));
		$sys_config_files['auth'] = gzinflate(base64_decode('ddDLCsIwEAXQtYH8QxZCdKfrUiFCqWJtSh8rkRA0xqImJUkR/15tC6KY7dwzMHMhGB+0OtWS8dadUYi4MfwxmQYQwK9oh0lVrhhN8f6lZsHftKSbKGUp2UadwlIobbkUzOmLUNizRJbJ4FsrjEdVRZR/kOI34YEZKYoeNtzauzZHD4xzWmXDnUa3jYclNF73T+OrlnX3xS90wrqO9O1BMOpHKFygOQTvNp8='));
		$sys_config_files['database'] = gzinflate(base64_decode('4+VSSc7PS8tMj09JLElMSixOVbBVSCwqSqzU0LTm5eLFkI5Wd3GK9/APDlGPBapUNzQy1zMAQkMrY2MDM3VrHBr8HH1dIRrSU/PyixPTU3EqDQ12DYIoLcrPL8GpLMAxOBiiDLeSIFc3zwiIopJ4kDIA'));

		file_put_contents('./Config/ConCore.php', '<?php'.$sys_config_files['core'].'?>');
		file_put_contents('./Config/ConRouter.php', '<?php'.$sys_config_files['router'].'?>');
		file_put_contents('./Config/ConAuth.php', '<?php'.$sys_config_files['auth'].'?>');
		file_put_contents('./Config/ConDatabase.php', '<?php'.$sys_config_files['database'].'?>');

		# > APP DEMO
		$sys_app_demo = gzinflate(base64_decode('4+VS5uVSVnAMCPD0c3GN4AVxk3MSi4sVHAsKPPNSUisUUitKUvNSwAK8XNW8XJwFpUk5mckKaaV5ySWZ+XkKmSBlGppAGZAsZ0FRZl5JfJGGkkdqTk6+Qnl+UU6KopKmNVCulpOXq5aXCwA='));

		file_put_contents('./App/C/AppIndex.php', '<?php'.$sys_app_demo.'?>');

		# > INIT CPMPLETE
	}
}
?>