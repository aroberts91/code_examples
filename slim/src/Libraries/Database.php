<?php

namespace App\Libraries;

use PDO;

class Database extends PDO
{
	public function construct($dsn, $username, $password, $opts)
	{
		parent::__construct($dsn, $username, $password, $opts);
	}

	/**
	 * Query the database using either prepared or normal statement, return one row
	 * @param string $sql The query
	 * @param mixed $data Any values used in a prepared statement
	 * @param bool $prepared Whether to use a prepared statement or not
	 * @return mixed An array containing the data from the query or false if unsuccessful
	 */
	public function dbFetch($sql, $data = FALSE, $prepared = TRUE)
	{
		if($prepared && (!$data || empty($data))) return FALSE;

		if( !$prepared ) {
			return $this->query($sql)->fetch();
		} else {
			$stmt = $this->prepare($sql);

			if( $stmt->execute($data) ) return $stmt->fetch();

			return FALSE;
		}
	}

	/**
	 * Query the database using either prepared or normal statement, return all rows
	 * @param string $sql The query
	 * @param mixed $data Any values used in a prepared statement
	 * @param bool $prepared Whether to use a prepared statement or not
	 * @return mixed An array containing the data from the query or false if unsuccessful
	 */
	public function dbFetchAll($sql, $data = FALSE, $prepared = TRUE)
	{
		//If no data is passed, try to default to normal statement
		if((!$data || empty($data))) $prepared = FALSE;

		if( !$prepared ) {
			return $this->query($sql)->fetchAll();
		} else {
			$stmt = $this->prepare($sql);

			if( $stmt->execute($data) ) return $stmt->fetchAll();

			return FALSE;
		}
	}

	/**
	 * Build a query to Add a new, or update an existing, row.
	 * @param string $table The table to be updated
	 * @param array $data The columns/values to be updated.
	 * @param mixed $id_key The name of the id column for this table, to be used when duplicate exists
	 * @return bool The success or failure
	 */
	public function dbAddUpdate($table, $data, $id_key = FALSE)
	{
		if( !$data || empty($data) ) return FALSE;

		foreach( $data as $key => $value ) {
			$insert[] = '`' . $key . '`';
			$values[] = ':' . $key;
			$update[] = '`' . $key . '`=VALUES(' . $key . ')';
		}

		$insert = implode(',', $insert);
		$values = implode(',', $values);
		$update = implode(',', $update);

		$q = 'INSERT INTO ' . $table . '(' . $insert . ') ';
		$q.= 'VALUES(' . $values . ') ';

		if( $id_key ) {
			$q.= 'ON DUPLICATE KEY UPDATE ' . $id_key . '= LAST_INSERT_ID(' . $id_key . '), ' . $update;
		}


		return $this->prepare($q)->execute($data);
	}

	/**
	 * Update a row, or multiple rows, (using update instead of on duplicate key)
	 * @param string $table
	 * @param array $data
	 * @param array $where
	 * @return bool
	 */

	public function dbUpdate($table, $data, $where)
	{
		foreach( $data as $key => $value ) {
			$args[] = $key . '=:' . $key;
		}
		$update = implode(',', $args);
		$args = [];

		foreach( $where as $key => $value ) {
			if( is_null($value) ) {
				$args[] = $key . ' IS NULL';
				continue;
			}

			$args[] = $key . '=' . $value;
		}

		$where = implode(' AND ', $args);

		$q = 'UPDATE ' . $table . ' ';
		$q.= 'SET ' . $update . ' ';
		$q.= 'WHERE ' . $where;

		return $this->prepare($q)->execute($data);
	}

	/**
	 * Delete a row
	 * @param string $table The table to delete from
	 * @param array $where An array of values to include in the WHERE clause
	 * @return bool
	 */
	public function dbDelete($table, $where)
	{
		foreach($where as $key => $value) {
			if(is_null($value)) {
				$args[] = $key . ' IS NULL';
			}

			$args[] = $key . '=:' . $key;
		}

		$where_str = implode(' AND ', $args);

		$q = 'DELETE FROM `' . $table . '` ';
		$q.= 'WHERE ' . $where_str;

		return $this->prepare($q)->execute($where);
	}
}
