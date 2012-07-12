<?php
class Sql extends Core
{
	private $dbhost;
	private $dbname;
	private $dbuser;
	private $dbpass;
	private $dbprefix;
	private $cache;
	private $status;
	private $_sql;
	private $_to;
	private $_field;
	private $_fields;
	private $_select;
	private $_update;
	private $_insert;
	private $_where;
	private $_limit;
	private $_order;
	private $_result;
	private $_db_handle;
	private $_query_handle;
		
	#
	# INIT
	#
	public function __construct()
	{
		parent::__construct('SQL');
		
		$this->status = FALSE;
		$this->cache = $this->core('Cache');
		
		$this->dbhost = $this->config['database']['DB_HOST'];
		$this->dbname = $this->config['database']['DB_NAME'];
		$this->dbuser = $this->config['database']['DB_USER'];
		$this->dbpass = $this->config['database']['DB_PASS'];
		$this->dbprefix = $this->config['database']['DB_PREFIX'];
	}

	# DESTRUCT
	public function __destruct()
	{
		# FREE DATA
		$this->free();
	}
	
	# CONN INIT
	private function init()
	{	
		# CONN DATABASE SERVER
		$this->_db_handle = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
		# CONN DATABASE
		mysql_select_db($this->dbname, $this->_db_handle);
		# SET CHARSET
		mysql_query('SET NAMES UTF8');
		# CHECK CONN RESULT
		if (!$this->_db_handle)
		{
			$this->error('DB_CONNECT_ERROR');
		}
		# 
		$this->status = TRUE;
	}
	
	# BUILD SQL
	private function build()
	{
		$this->_sql  = '';
		$this->_sql .= isset($this->_select)?'SELECT '.$this->_select:'';
		$this->_sql .= isset($this->_to)?' FROM '.$this->_to:'';
		$this->_sql .= isset($this->_where)?' WHERE '.$this->_where:'';	
		$this->_sql .= isset($this->_order)?' ORDER BY '.$this->_order:'';
		$this->_sql .= isset($this->_limit)?' LIMIT '.$this->_limit:'';
		return;
	}
	
	# COMPILED
	private function compiled($sql)
	{	
		$sql = str_replace('{%', '[%]:>', $sql);
		$sql = str_replace('%}', '[%]', $sql);
		
		$sql_stream = explode('[%]', $sql);
		
		for ($i=0; $i<count($sql_stream); $i++)
		{
			if (strstr($sql_stream[$i], ':>') && strpos($sql_stream[$i], ':>') == 0)
			{
				$sql_stream[$i] = $this->v(substr($sql_stream[$i], 2));
			}
		}
		
		return implode($sql_stream);
	}
	
	# EXECUTE SQL
	public function query()
	{	
		# SQL
		$sql = func_get_arg(0);
		$args_count = func_num_args();
				
		if ($args_count > 1)
		{	
			# ' => "
			$sql = str_replace('"', '\'', $sql);
				
			for ($i=1; $i<$args_count; $i++)
			{
				$_arg = func_get_arg($i);
				$sql = str_replace('{%'.$i.'%}', $_arg, $sql);
			}	
		}
		
		# COMPILED
		$sql = $this->compiled($sql);
		
		$this->debug('db', $sql);
			
		# CHECK CACHE
		if ($this->cache->cache_check($sql, 'db'))
		{		
			$this->free();	
			return $this->cache->cache_get($sql);
		}
		
		# INIT
		if (!$this->status)
		{
			$this->init();
		}

		# ANALYZE TYPE
		$type = strtolower(substr($sql, 0, 6));
	
		# QUERY SQL
		$this->_query_handle = mysql_query($sql, $this->_db_handle);
		
		# DEAL WITH RESULT
		if (!$this->_query_handle)
		{
			$this->error('QIERY_SQL_ERROR:'.strtoupper($type).':'.$sql);
		}
		else
		{
			switch ($type)
			{
				case 'select':
					$this->_result = array();
					if (mysql_num_rows($this->_query_handle)>0)
					{
						while ($row = mysql_fetch_assoc($this->_query_handle))
						{
							$this->_result[] = $row;
						}
						mysql_data_seek($this->_query_handle, 0);
					}
					else
					{
						$this->_result = FALSE;
					}
					
					# WRITE CACHE
					$this->cache->cache_save($sql, $this->_result, 'db');
					# DEBUG
					$this->debug('db_read');
																
					break;
				case 'update':
					$this->_result = mysql_affected_rows($this->_db_handle);
					# DEBUG
					$this->debug('db_write');
					
					break;
				case 'insert':		
					mysql_affected_rows($this->_db_handle);
					$this->_result = mysql_insert_id();
					# DEBUG
					$this->debug('db_write');
					
					break;
				case 'delete':
					$this->_result = mysql_affected_rows($this->_db_handle);
					# DEBUG
					$this->debug('db_write');
					
					break;
				default: break;
			}
			
			$this->free();
			return $this->_result;
		}		
		
	}
	
	# TARGET TABLE
	public function to($to)
	{
		$this->free();	
		$this->_to = strtolower($this->dbprefix.$to);
		return $this;
	}
	
	# SELECT
	public function select($select = '*')
	{
		$this->_select = $select;
		return $this;
	}
	
	# WHERE
	public function where($where)
	{
		$this->_where = $where;
		return $this;
	}
	
	# FIELD
	public function field($field)
	{
		$this->_field = $field;
		return $this;
	}
	
	# LIMIT
	public function limit($limit_from=NULL, $limit_count=NULL)
	{
		if (isset($limit_from) && isset($limit_count))
		{
			$this->_limit = $limit_from.','.$limit_count;
		}
		else
		{
			$this->_limit = NULL;
		}		
		return $this;
	}
	
	# ORDER
	public function order($order)
	{
		$this->_order = $order;
		return $this;
	}
	
	# UPDATE
	public function update($update)
	{
		$this->_sql  = '';
		$this->_sql .= 'UPDATE ';
		$this->_sql .= isset($this->_to)?$this->_to:'';
		$this->_sql .= ' SET ';
		
		if (is_array($update))
		{
			$temp = '';
			$i = 0;
			foreach( $update as $key => $val )
			{	
				if (strstr($val, '[CON]') && strpos($val, '[CON]') == 0)
				{
					$temp .= $key.'='.substr($val, 5);
					continue;
				}
				if (strstr($val, '[REP]') && strpos($val, '[REP]') == 0)
				{
					$temp .= $key.'='.substr($val, 5);
					continue;
				}
				if (strstr($val, '[SEL]') && strpos($val, '[SEL]') == 0)
				{
					$temp .= $key.'='.substr($val, 5);
					continue;
				}
				
				if (is_string($val))
				{					
					$temp .= '`'.$key.'`=\''.$val.'\'';
				}
				else
				{
					$temp .= '`'.$key.'`='.$val;
				}
				
				if ($i < count($update) - 1)
				{
					$temp .= ',';
				}
				$i++;
			}	
		}
		
		$this->_sql .= $temp;
		$this->_sql .= isset($this->_where)?' WHERE '.$this->_where:'';
		
		return $this->query($this->_sql);
	}
	
	# INSERT
	public function insert($insert)
	{
		$this->_sql  = '';
		$this->_sql .= 'INSERT INTO ';
		$this->_sql .= isset($this->_to)?$this->_to:'';
		
		if (is_array($insert))
		{
			$temp_a = '';
			$temp_b = '';
			$i = 0;
			foreach( $insert as $key => $val )
			{
				$temp_a .= '`'.$key.'`';
				
				if (is_string($val))
				{
					$temp_b .= '\''.$val.'\'';
				}
				else
				{
					$temp_b .= $val;
				}
				
				if ($i < count($insert) - 1)
				{
					$temp_a .= ',';
					$temp_b .= ',';
				}
				$i++;
			}	
		}
		
		$this->_sql .= ' ('.$temp_a.') VALUES ('.$temp_b.')';
		return $this->query($this->_sql);
	}
	
	# DELETE
	public function delete($id = 0)
	{	
		if ($id <> 0)
		{
			$this->where('id='.$id);
		}
		
		$this->_sql  = '';
		$this->_sql .= 'DELETE ';
		$this->_sql .= isset($this->_to)?' FROM '.$this->_to:'';
		$this->_sql .= isset($this->_where)?' WHERE '.$this->_where:'';
			
		return $this->query($this->_sql);
	}
		
	# GET SELECT RESULT
	public function get()
	{	
		$this->build();	
		return $this->query($this->_sql);
	}
	
	# GET DATA COUNT
	public function count()
	{
		$this->_select = 'count(*)';
		$this->build();
		$_count = $this->query($this->_sql);
		return $_count[0]['count(*)'];
	}
	
	# EXIST
	public function exist()
	{		
		if ($this->count() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	# CHANGE
	public function self($change)
	{
		$data[$this->_field] = '[SEL]'.$this->_field.$change;
		$this->update($data);
	}
	
	# APPEND
	public function append($content)
	{
		$data[$this->_field] = '[CON]CONCAT('.$this->_field.', \''.$content.'\')';
		$this->update($data);
	}
	
	# REPLACE
	public function replace($target, $content)
	{
		$data[$this->_field] = '[REP]REPLACE('.$this->_field.', \''.$target.'\', \''.$content.'\')';
		$this->update($data);
	}
	
	# FIELD LIST
	public function fields($table)
	{
		# CHECK CACHE
		if ($this->cache->cache_check('FIELDS:'.$table, 'db'))
		{
			return $this->cache->cache_get('FIELDS:'.$table);
		}
		
		# INIT
		if (!$this->status)
		{
			$this->init();
		}
		
		# GET FIELDS
		$fields = mysql_list_fields($this->dbname, $this->dbprefix.$table, $this->_db_handle);
		$field_count = mysql_num_fields($fields); 
	
		for ($i=0; $i<$field_count; $i++) 
		{ 
			$this->_fields[] = mysql_field_name($fields, $i);
		} 
		
		# WRITE CACHE
		$this->cache->cache_save('FIELDS:'.$table, $this->_fields, 'db');
			
		return $this->_fields;
	}
	
	# FREE
	public function free()
	{
		unset($this->_sql);
		unset($this->_to);
		unset($this->_select);
		unset($this->_update);
		unset($this->_insert);
		unset($this->_where);
		unset($this->_limit);
		unset($this->_order);	
	}
}
?>