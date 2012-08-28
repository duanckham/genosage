<?php
class Sql extends Core
{
	private $_dbhost;
	private $_dbname;
	private $_dbuser;
	private $_dbpass;
	private $_dbprefix;
	private $_cache;
	private $_status;
	private $_sql;
	private $_to;
	private $_field;
	private $_select;
	private $_update;
	private $_insert;
	private $_where;
	private $_in;
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
		
		$this->_status = FALSE;
		$this->_cache = $this->core('Cache');
		
		$this->_dbhost = $this->con('DATABASE.DB_HOST');
		$this->_dbname = $this->con('DATABASE.DB_NAME');
		$this->_dbuser = $this->con('DATABASE.DB_USER');
		$this->_dbpass = $this->con('DATABASE.DB_PASS');
		$this->_dbprefix = $this->con('DATABASE.DB_PREFIX');
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
		$this->_db_handle = mysql_connect($this->_dbhost, $this->_dbuser, $this->_dbpass);
		# CONN DATABASE
		mysql_select_db($this->_dbname, $this->_db_handle);
		# SET CHARSET
		mysql_query('SET NAMES UTF8');
		# CHECK CONN RESULT
		if (!$this->_db_handle)
		{
			$this->error('DB_CONNECT_ERROR');
		}
		# 
		$this->_status = TRUE;
	}
	
	# BUILD SQL
	private function build()
	{
		$this->_sql  = '';
		$this->_sql .= isset($this->_select)?'SELECT '.$this->_select:'';
		$this->_sql .= isset($this->_to)?' FROM '.$this->_to:'';
		$this->_sql .= isset($this->_where)?' WHERE '.$this->_where:'';
		$this->_sql .= isset($this->_in)?' WHERE '.$this->_in:'';
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
			for ($i=1; $i<$args_count; $i++)
			{
				$_arg = func_get_arg($i);
				$sql = str_replace('{%'.$i.'%}', $_arg, $sql);
			}	
		}
		
		# COMPILED
		$sql = $this->compiled($sql);
		# DEBUG
		$this->debug('DB', $sql);

		# CHECK CACHE
		if ($this->_cache->cache_check($sql, 'db'))
		{		
			$this->free();	
			return $this->_cache->cache_get($sql);
		}
		
		# INIT
		if (!$this->_status)
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
			$this->error('QUERY_SQL_ERROR:'.strtoupper($type).':'.$sql);
		}
		else
		{
			switch ($type)
			{
				case 'select':			
					if (mysql_num_rows($this->_query_handle) > 0)
					{
						$this->_result = array();
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
					$this->_cache->cache_save($sql, $this->_result, 'db');
					# DEBUG
					$this->debug('DB:READ');
																
					break;
				case 'update':
					$this->_result = mysql_affected_rows($this->_db_handle);
					# DEBUG
					$this->debug('DB:WRITE');
					
					break;
				case 'insert':		
					mysql_affected_rows($this->_db_handle);
					$this->_result = mysql_insert_id();
					# DEBUG
					$this->debug('DB:WRITE');
					
					break;
				case 'delete':
					$this->_result = mysql_affected_rows($this->_db_handle);
					# DEBUG
					$this->debug('DB:WRITE');
					
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
		$this->_to = strtolower($this->_dbprefix.$to);
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

	# IN
	public function in($in_key, $in_arr)
	{
		if (is_string($in_arr))
		{
			$this->_in = $in_key.' IN ('.$in_arr.')';
		}
		else if (is_string($in_arr[0]))
		{
			$this->_in = $in_key.' IN (\''.implode('\',\'', $in_arr).'\')';
		}
		else
		{
			$this->_in = $in_key.' IN ('.implode(',', $in_arr).')';
		}
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
				
				if (is_null($val))
				{
					$temp .= '`'.$key.'`=NULL';
				}
				else if (is_string($val))
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
				
				if (is_null($val))
				{
					$temp_b .= 'NULL';
				}
				else if (is_string($val))
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
		if ($this->_cache->cache_check('FIELDS:'.$table, 'db'))
		{
			return $this->_cache->cache_get('FIELDS:'.$table);
		}
		
		# INIT
		if (!$this->_status)
		{
			$this->init();
		}
		
		# GET FIELDS
		$this->_query_handle = mysql_query('DESCRIBE '.$this->_dbprefix.$table, $this->_db_handle);

		if (!$this->_query_handle)
		{
			$this->_result = FALSE;
		}
		else
		{
			if (mysql_num_rows($this->_query_handle) > 0)
			{
				while ($row = mysql_fetch_assoc($this->_query_handle))
				{
					if ($row['Key'] == 'PRI')
					{
						$this->_result['PRIKEY'] = $row['Field'];
					}
					$this->_result[] = $row['Field'];
				}
				mysql_data_seek($this->_query_handle, 0);
			}
			else
			{
				$this->_result = FALSE;
			}
		}

		# WRITE CACHE
		$this->_cache->cache_save('FIELDS:'.$table, $this->_result, 'db');

		return $this->_result;
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