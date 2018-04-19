<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/3/18
 * Time: 12:00 PM
 */

namespace framework\database;

use \PDO as PDO;
use \PDOException as PDOException;

class DBi
{
    protected $host_server = "localhost";

    protected $username = "root";

    protected $password = "";

    protected $database = "school_msys_db";

    protected $connection = null;

    protected static $instance;     // For creating a singleton

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function destroyConnection(){
        $this->connection = null;
    }

    private function __construct() {
        try {
            $this->connection = new PDO("mysql:host=" . $this->host_server . ";dbname=" . $this->database, $this->username, $this->password);
            // set the PDO error mode to exception
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            die ("Connection failed: " . $e->getMessage());
        }
    }

    private function __clone() {
        // Magic method hinders duplicates
    }

    // Get PDO connection
    public function getConnection() {
        return $this->connection;
    }

    public function query($query) {
//        return $results;
    }
    public function insert($table, $data, $format) {
//        return bool;
    }

    public function update($table, $data, $format, $where, $where_format) {
//        return bool;

    }
    public function select($query, $data, $format) {
//        return $results;
    }
    public function delete($table, $id) {
//        return bool;
    }
    private function prep_query($data, $type='insert') {
        // Instantiate $fields and $placeholders for looping
        $fields = '';
        $placeholders = '';
        $values = array();

        // Loop through $data and build $fields, $placeholders, and $values
        foreach ( $data as $field => $value ) {
            $fields .= "{$field},";
            $values[] = $value;

            if ( $type == 'update') {
                $placeholders .= $field . '=?,';
            } else {
                $placeholders .= '?,';
            }

        }

        // Normalize $fields and $placeholders for inserting
        $fields = substr($fields, 0, -1);
        $placeholders = substr($placeholders, 0, -1);

        return array( $fields, $placeholders, $values );
    }
    private function ref_values($array) {
        $refs = array();
        foreach ($array as $key => $value) {
            $refs[$key] = &$array[$key];
        }
        return $refs;
    }

}