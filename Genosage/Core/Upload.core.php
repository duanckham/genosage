<?php
class Upload extends Core
{
	public $allow_size;
	public $allow_ext;
	public $save_path;
	public $save_name;
	public $file_field;
	public $file_ext;

	public function __construct()
	{
		parent::__construct('UPLOAD');
		
		$this->allow_size = isset($this->allow_size) ? $this->allow_size : 2097152;
		$this->allow_ext = isset($this->allow_ext) ? $this->allow_ext : array('jpg', 'gif', 'png', 'jpeg');
		$this->save_path = isset($this->save_path) ? $this->save_path : './Upload/';
		$this->save_name = isset($this->save_name) ? $this->save_name : $this->hash(date('YmdHis'));
		$this->file_field = isset($this->file_field) ? $this->file_field : 'file';
	}

	public function upload($field = FALSE)
	{
		if ($field)
		{
			$this->file_field = $field;
		}
			
		if ($_FILES[$this->file_field]['error'] > 0)
		{
			$this->error('FILE_UPLOAD_ERROR:'.$_FILES[$this->file_field]['error']);
		}
		else
		{
			$this->get_ext(basename($_FILES[$this->file_field]['name']));

			$file_info['name'] = $this->save_name.'.'.$this->file_ext;
			$file_info['size'] = $_FILES[$this->file_field]['size'];
			$file_info['path'] = $this->save_path;

			if ($file_info['size'] > $this->allow_size)
			{
				$this->error('UPLOAD_FILE_SIZE_ERROR:ALLOW_SIZE:'.$this->allow_size);
			}

			if (!in_array(strtolower($this->file_ext), $this->allow_ext, true))
			{
				$this->error('UPLOAD_FILE_EXT_ERROR');
			}
		
			move_uploaded_file($_FILES[$this->file_field]['tmp_name'], $file_info['path'].$file_info['name']);

			return $file_info;
		}
	}

	private function get_ext($file_name)
	{
		$pathinfo = pathinfo($file_name);
        $this->file_ext = $pathinfo['extension'];
	}
}
?>