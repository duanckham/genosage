<?php
#
#
#
class Mod extends Core
{
	public $data;
	public $model_name;
	public $table;
	public $db;
	public $result;

	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('MOD');
	}

	public function load($model)
	{
		# INIT
		$this->data = array();
		$this->result = array();
		$this->table = strtolower($model);
		$this->db = $this->core('Sql');
		$this->model_name = $model;
	}
	
	# QUERY SQL
	public function query($sql)
	{
		return $this->db->query($sql);
	}
	
	# AMOUNT OF DATA
	public function count($condition=NULL)
	{
		return $this->db->to($this->table)->select('*')->where($condition)->count();
	}
	
	# FIND DATA
	public function find($field='*', $condition=NULL, $from=NULL, $count=NULL, $order=NULL)
	{
		$this->result = $this->db->to($this->table)->select($field)->where($condition)->limit($from, $count)->order($order)->get();
		return $this->result;
	}
	
	# FIND THE FIRST DATA FROM RESULT
	public function first($field='*', $condition=NULL)
	{
		$this->result = $this->db->to($this->table)->select($field)->where($condition)->limit(0,1)->get();
		return $this->result;
	}
	
	# FIND THE LAST DATA FROM RESULT
	public function last($field='*', $condition=NULL)
	{
		$this->result = $this->db->to($this->table)->select($field)->where($condition)->get();
		end($this->result);
		return current($this->result);
	}
		
	# READ DATA
	public function read($field, $value)
	{	
		# GET CONDITION
		if (is_int($value))
		{
			$condition = $field.'='.$value;
		}
		else
		{
			$condition = $field.'=\''.$value.'\'';
		}
		
		$this->result = $this->db->to($this->table)->select('*')->where($condition)->get();
		$this->data = $this->result[0];
		return $this->data;
	}
	
	# SAVE DATA
	public function save($arr=NULL)
	{
		# GET DATA
		if (isset($arr))
		{
			$this->data = $arr;
		}
		
		# GET CONDITION
		$_key = array_keys($this->data);
		$_value = array_values($this->data);
		
		if (is_int($this->data[$_key[0]]))
		{
			$condition = $_key[0].'='.$_value[0];
		}
		else
		{
			$condition = $_key[0].'=\''.$_value[0].'\'';
		}
		
		# UPDATE OR INSERT
		if ($this->db->to($this->table)->where($condition)->exist())
		{
			return $this->db->to($this->table)->where($condition)->update($this->data);
		}
		else
		{
			return $this->db->to($this->table)->insert($this->data);
		}		
	}
	
	# DELETE DATA
	public function delete($condition)
	{
		return $this->db->to($this->table)->where($condition)->delete();
	}
}
?>