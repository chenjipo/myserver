<?php

namespace YXLib\foundation;

use YXLib\foundation\database\LibMysql;

class Model
{
	/**
	 * @var mixed db对象
	 */
	static $db;

	static $posix_id;

	/**
	 * @var mixed 指定当前数据库连接的库
	 */
	protected $conn = 'default';

	/**
	 * @var mixed 指定当前模型所使用的表
	 */
	protected $table = '';

	/**
	 * 添加初始化方法，以在简单增删改查情况下直接使用此类而不用再新建model类
	 * @param string $conn DB的name
	 * @param string $table 表名
	 */
	public function __construct($conn = '', $table = '')
	{
		if ($conn) {
			$this->conn = $conn;
		}
		if ($table) {
			$this->table = $table;
		}
	}

	/**
	 * 连接DB
	 * @param string $name DB的name
	 * @param int $dbIndex DB的序号，针对多库的情况
	 * @return LibMysql
	 */
	protected function connDb($name = null, $dbIndex = 0)
	{
		if (empty(static::$db) || static::$posix_id != posix_getpid()) {
			$libMysql = new LibMysql();
			static::$posix_id = posix_getpid();
			static::$db = $libMysql;
		} else {
			$libMysql = static::$db;
		}

		$name = is_null($name) ? $this->conn : $name;
		$libMysql->useDb($name, $dbIndex);
		return $libMysql;
	}

	/**
	 * 执行SQL，如果是select语句则返回数组，其他返回结果
	 *
	 * @param string $sql SQL
	 * @param array $parameter 绑定变量
	 * @param array $mapField map的键值
	 * @return array|bool|resource|string
	 */
	public function query($sql, $parameter = array(), $mapField = '')
	{
		if (!$this->conn) {
			Debug::log('模型没有执行数据ID', 'error');
			return false;
		}
		return $this->connDb($this->conn)->query($sql, $parameter, $mapField);
	}

	/**
	 * 返回一条数据
	 *
	 * @param string $sql SQL
	 * @param array $parameter 绑定变量
	 * @return array|bool|resource|string
	 */
	public function getOne($sql, $parameter = array())
	{
		if (!$this->conn) {
			Debug::log('模型没有执行数据ID', 'error');
			return false;
		}
		return $this->connDb($this->conn)->getOne($sql, $parameter);
	}

	/**
	 * 通用获取一条数据方法
	 * @param $table
	 * @param $field
	 * @param $value
	 * @return array|bool|resource|string
	 */
	public function commonGetOne($table, $field, $value = '')
	{
		if (is_array($field)) {
			$sql = "select * from {$table} where 1 ";
			foreach ($field as $k => $v) {
				$sql .= " and `{$k}`=:{$k} ";
			}
			return $this->getOne($sql, $field);
		} else {
			$sql = "select * from {$table} where `{$field}`=:value ";
			return $this->getOne($sql, array('value' => $value));
		}
	}

	/**
	 * 开启事务
	 *
	 * @return void
	 */
	public function startWork()
	{
		if (!$this->conn) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->execute("START TRANSACTION; ");
	}

	/**
	 * 回滚事务
	 *
	 * @return void
	 */
	public function rollBack()
	{
		if (!$this->conn) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->execute("rollback;");
	}

	/**
	 * 提交事务
	 *
	 * @return void
	 */
	public function commit()
	{
		if (!$this->conn) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->execute("commit;");
	}

	/**
	 * 插入数据
	 *
	 * @param array $data 插入的数据 k-v
	 * @param bool $returnId 是否返回ID
	 * @param string $table 表名
	 * @return resource|string
	 */
	public function insert($data, $returnId = false, $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		$re = $this->connDb($this->conn)->insert($this->table, $data);
		if ($returnId && $re) {
			return $this->connDb($this->conn)->insertId();
		}
		return $re;
	}

	public function multiInsert($data, $table = "", $ignore = false)
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		$re = $this->connDb($this->conn)->multiInsert($this->table, $data, $ignore);

		return $re;
	}

	public function ignoreInsert($data, $returnId = false, $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		$re = $this->connDb($this->conn)->insert($this->table, $data, true);
		if ($returnId && $re) {
			return $this->connDb($this->conn)->insertId();
		}
		return $re;
	}

	/**
	 * 插入或者更新数据
	 *
	 * @param array $insertData 插入的数据
	 * @param array $updateData 更新的数据
	 * @param string $table 表名
	 * @return resource|string
	 */
	public function insertOrUpdate($insertData, $updateData, $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		$re = $this->connDb($this->conn)->insertOrUpdate($this->table, $insertData, $updateData);
		return $re;
	}



	/**
	 * 修改数据
	 *
	 * @param array $data 需要修改的数据 k-v
	 * @param array|string $where where字句
	 * @param string $table 表名
	 * @return resource|string
	 */
	public function update($data, $where, $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->update($this->table, $data, $where);
	}

	public function affectedRows()
	{
		$re = $this->connDb($this->conn)->affectedRows();
		return $re;
	}

	/**
	 * 删除数据
	 *
	 * @param array|string $where where字句
	 * @param int $limit 限制影响的条数
	 * @param string $table 表名
	 * @return resource|string
	 */
	public function delete($where, $limit = 0, $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->delete($this->table, $where, $limit);
	}

	/**
	 * 根据ID返回数据
	 *
	 * @param int $id ID
	 * @param string $field 字段
	 * @param string $idName ID的字段名
	 * @param string $table 表名
	 * @return array
	 */
	public function getById($id, $field = '*', $idName = 'id', $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->getById($this->table, $id, $field, $idName);
	}

	/**
	 * 获取多条记录
	 *
	 * @param string $field
	 * @param array $params
	 * @param string $limit
	 * @param array $orderBy
	 * @param string $table
	 * @return array|false
	 */
	public function getMany($field = '*', $params = array(), $limit = '', $orderBy = [], $table = '')
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}
		return $this->connDb($this->conn)->getMany($this->table, $params, $field, $limit, $orderBy);
	}

	/**
	 * 统计条数
	 *
	 * @param [type] $attri
	 * @param string $table
	 * @return void
	 */
	public function getCount($attri, $table = "")
	{
		if ($table != "") $this->table = $table;
		if (!$this->conn || !$this->table) {
			Debug::log('模型没有执行数据ID或者需要操作的数据表', 'error');
			return false;
		}

		return $this->connDb($this->conn)->getCount($this->table, $attri);
	}

	/**
	 * 返回分页limit
	 *
	 * @param int $page 分数
	 * @param int $num 每页数量
	 * @return string
	 */
	public function getLimit($page = 1, $num = 1)
	{
		$page = intval($page);
		if ($page < 1) $page = 1;
		$num = intval($num);
		if ($num < 1) {
			$num = 10;
		}
		if ($num > 100) {
			$num = 100;
		}
		$offset = ($page - 1) * $num;
		$limit = " limit {$offset}, {$num}";
		return $limit;
	}
}
