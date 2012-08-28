<?php
class Mod extends Core
{
	private $_data;
	private $_items;
	private $_table_name;
	private $_table_fields;
	private $_table_prikey;	
	
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('MOD');
		$this->sql = $this->core('Sql');
	}

	#
	# LOAD MODEL
	#
	public function load($model)
	{
		# INIT
		$this->_table_name = strtolower($model);
		$this->_table_fields = $this->sql->fields($model);
		$this->_table_prikey = $this->_table_fields['PRIKEY'];

		return $this;
	}

	#
	# CREATE
	#
	public function create()
	{
		$obj = $this->m($this->_table_name);

		for ($i=0; $i<count($this->_table_fields)-1; $i++)
		{
			$field_name = $this->_table_fields[$i];
			$obj->$field_name = NULL;
		}

		return $obj;
	}

	#
	# QUERY SQL
	#
	public function query($sql)
	{
		return $this->sql->query($sql);
	}

	#
	# FIND OBJ BY PRIKEY OR OTHER FIELD
	#
	public function find($val, $field=NULL)
	{
		if (isset($field))
		{
			if (is_int($val))
			{
				$condition = $field.'='.$val;
			}
			else
			{
				$condition = $field.'=\''.$val.'\'';
			}
			
			$this->_data = $this->sql->to($this->_table_name)->select()->where($condition)->get();
		}
		else
		{
			$this->_data = $this->sql->to($this->_table_name)->select()->where($this->_table_prikey.'='.$val)->get();
		}

		if ($this->_data)
		{
			if (count($this->_data) == 1)
			{
				foreach ($this->_data[0] as $key => $value)
				{
					$this->$key = $value;
				}
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
	public function save($arr=NULL)
	{
		# GET DATA
		if (isset($arr))
		{
			if (count($this->_data) == 1)
			{
				return $this->sql->to($this->_table_name)->where($this->_table_prikey.'='.$this->_data[0][$this->_table_prikey])->update($arr);
			}
			else
			{		
				return $this->sql->to($this->_table_name)->insert($arr);
			}
		}
		else
		{
			for ($i=0; $i<count($this->_table_fields)-1; $i++)
			{
				$field_name = $this->_table_fields[$i];
				$arr[$this->_table_fields[$i]] = $this->$field_name;
			}

			if (count($this->_data) == 0)
			{
				return $this->sql->to($this->_table_name)->insert($arr);
			}

			if (count($this->_data) == 1)
			{
				return $this->sql->to($this->_table_name)->where($this->_table_prikey.'='.$arr[$this->_table_prikey])->update($arr);
			}
		}
	}
	
	#
	# DELETE DATA
	#
	public function delete()
	{
		return $this->sql->to($this->_table_name)->where($this->_table_prikey.'='.$this->_data[0][$this->_table_prikey])->delete();
	}

	#
	# ITEM
	#
	public function item($id)
	{
		foreach ($this->_data[$id] as $key => $value)
		{
			$this->$key = $value;
		}

		return $this;
	}

	#
	# DATA
	#
	public function data($id=NULL)
	{
		if (isset($id))
		{
			return $this->_data[$id];
		}
		else
		{
			return $this->_data;
		}
	}
}
?>