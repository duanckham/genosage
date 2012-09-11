<?php
class Init extends Core
{
	static function genosage()
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
		$sys_config_files['core'] = gzinflate(base64_decode('4+VSSc4vSlWwVUgsKkqs1NC05uXihYhFq4cGuDiGuMaHePq6qscClagbGRgaGVgaGqpbw9U4+/t7e7rG+3i6ISm0MDMxMEBS4+js4RofHOmHUGGMkHVxdQp1j/cMgFhhaGSuZwCEQDsUlBVCg10VNHQ0FUL8FYJdAxyDgM5x4eUCAA=='));
		$sys_config_files['auth'] = gzinflate(base64_decode('4+VSSSwtyVCwVUgsKkqs1NC05uXihYhFqzuGhnjE+/upxwKlDaxRhUP8vV394v0cfV3B0urpqXn5xYnpqfEl+dmpeeroqh2dfKAKQ4tTi9ClQ4NdgyCypUDZvMTcVHQVAY7BwRAVBYnFxeX5RSnoKtyD/EMDoG4pyi8tQJf38Xf3hHhFvTgzPS8T7Ea4Ei2wDCQMeLk4gXwFWzsFA14uUIAAAA=='));
		$sys_config_files['database'] = gzinflate(base64_decode('4+VSSUksSUxKLE5VsFVILCpKrNTQtObl4kWIR6u7OMV7+AeHqMcClagbGpnrGQChoZWxsYGZujW6Sj9HX1eIyvTUvPzixPRUTDWhwa5BEDVF+fklmPIBjsHBEHksckGubp4RUBvyitPjQUoA'));
		$sys_config_files['router'] = gzinflate(base64_decode('4+VSKcovLUktUrBVSCwqSqzU0LTm5eKFiUarZ+alpFaox8Klebk41bXUFWztFNQ9QVL6EAW8XCB9cF1axOoAAA=='));

		file_put_contents('./Config/ConCore.php', '<?php'.$sys_config_files['core'].'?>');
		file_put_contents('./Config/ConRouter.php', '<?php'.$sys_config_files['router'].'?>');
		file_put_contents('./Config/ConAuth.php', '<?php'.$sys_config_files['auth'].'?>');
		file_put_contents('./Config/ConDatabase.php', '<?php'.$sys_config_files['database'].'?>');

		# > APP DEMO
		$sys_app_demo = gzinflate(base64_decode('4+VS5uVSVnAMCPD0c3GN4AVxk3MSi4sVHAsKPPNSUisUUitKUvNSwAK8XNW8XJwFpUk5mckKaaV5ySWZ+XkKmSBlGppAGZAsZ0FRZl5JfJGGukdqTk6+Qnl+UU6KorqmNVCulpOXq5aXCwA='));

		file_put_contents('./App/C/AppIndex.php', '<?php'.$sys_app_demo.'?>');

		# > INIT CPMPLETE
	}
}
?>