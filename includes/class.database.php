<?php

// $Id: class.database.php 342 2006-05-03 08:10:15Z stefan $

/*
Database class

This class will be used to interface between the database
and the Website Baker code
*/

// Stop this file from being accessed directly


define('CLASS_DATABASE_LOADED', true);

class database {
	function database($url = '') {
		// Connect to database
		$this->connect();
		// Check for database connection error
		if($this->is_error()) {
			die($this->get_error());
		}
	}
	// Connect to the database
	function connect() {
		$status = $this->db_handle = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
		if(mysql_error()) {
			$this->connected = false;
			$this->error = mysql_error();
		} else {
			if(!mysql_select_db(DB_NAME)) {
				$this->connected = false;
				$this->error = mysql_error();
			} else {
				$this->connected = true;
			}
		}
		return $this->connected;
	}
	// Disconnect from the database
	function disconnect() {
		if($this->connected==true) {
			mysql_close();
			return true;
		} else {
			return false;
		}
	}
	// Run a query
	function query($statement) {
		$mysql = new mysql();
		$mysql->query($statement);
		$this->set_error($mysql->error());
		if($mysql->error()) {
			return null;
		} else {
			return $mysql;
		}
	}
	// Gets the first column of the first row
	function get_one($statement) {
		$fetch_row = mysql_fetch_row(mysql_query($statement));
		$result = $fetch_row[0];
		$this->set_error(mysql_error());
		if(mysql_error()) {
			return null;
		} else {
			return $result;
		}
	}
	// Set the DB error
	function set_error($message = null) {
		global $TABLE_DOES_NOT_EXIST, $TABLE_UNKNOWN;
		$this->error = $message;
		if(strpos($message, 'no such table')) {
			$this->error_type = $TABLE_DOES_NOT_EXIST;
		} else {
			$this->error_type = $TABLE_UNKNOWN;
		}
	}
	// Return true if there was an error
	function is_error() {
		return (!empty($this->error)) ? true : false;
	}
	// Return the error
	function get_error() {
		return $this->error;
	}
	function insert_id() {
		return mysql_insert_id();
	}
}

class mysql {
	// Run a query
	function query($statement) {
		$this->result = mysql_query($statement);
		$this->error = mysql_error();
		return $this->result;
	}
	// Fetch num rows
	function numRows() {
		return mysql_num_rows($this->result);
	}
	function numFields() {
		return mysql_num_fields($this->result);
	}
	// Fetch row
	function fetchRow() {
		return mysql_fetch_array($this->result);
	}
	// Fetch assoc
	function fetchAssoc() {
		return mysql_fetch_assoc($this->result);
	}
	// Get error
	function error() {
		if(isset($this->error)) {
			return $this->error;
		} else {
			return null;
		}
	}
}

