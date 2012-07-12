<?php
#
#
#
class Orm extends Core
{
	private $_table_name;
	private $_table_fields;
	private $_db;
	private $_id;
	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('ORM');
		
		$this->db = $this->core('Sql'); 
	}
	
	#
	# LOAD TABLE
	#
	public function load_table($table)
	{
		$this->_table_name = $table;
		$this->_table_fields = $this->db->fields($table);
		return $this;
	}
	
	#
	# GET OBJ BY ID
	#
	public function id($id)
	{
		$data_result = $this->db->to($this->_table_name)->select()->where('id='.$id)->get();
		
		if ($data_result)
		{
			foreach ($data_result[0] as $key => $value)
			{
				$this->$key = $value;
			}
			return $this;
		}
		else
		{
			return FALSE;
		}	
	}
	
	#
	# GET OBJ BY OTHER FIELD
	#
	public function get($field, $value)
	{
		if (is_int($value))
		{
			$condition = $field.'='.$value;
		}
		else
		{
			$condition = $field.'=\''.$value.'\'';
		}
		
		$data_result = $this->db->to($this->_table_name)->select()->where($condition)->get();
		
		if ($data_result)
		{
			foreach ($data_result[0] as $key => $value)
			{
				$this->$key = $value;
			}
			return $this;
		}
		else
		{
			return FALSE;
		}
	}
	
	#
	# SAVE DATA
	#
	public function save()
	{
		for ($i=0; $i<count($this->_table_fields); $i++)
		{
			$field_name = $this->_table_fields[$i];	
			$data[$this->_table_fields[$i]] = $this->$field_name;
		}	
		
		return $this->db->to($this->_table_name)->where('id='.$this->id)->update($data);
	}
	
	#
	# DELETE DATA
	#
	public function delete()
	{
		return $this->db->to($this->_table_name)->where('id='.$this->id)->delete();
	}	
}
?>