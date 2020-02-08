<?php
namespace Illuminate\Database;
use Yaf_Registry;

use Yaf_Config_Ini;

class Database {


    public $databases;

    private static $instance = null;

    protected $_table;

    protected $_join;

    protected $_where;

    protected $_field;

    protected $_order;

    protected $_limit;

    public static $_error = ['code'=>101, 'msg'=>'', 'res'=>[]];

    public static $_pages = ['rows'=>[], 'total'=>0];

    private function __construct(){
        $databases = Yaf_Registry::get('database')->db->default->toArray();
        $this->databases = new Medoo($databases);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone(){

    }

    public function table($table){
        $this->_table = $table;
        return $this;
    }

    public function join($join){
        $this->_join = $join;
        return $this;
    }

    public function where($where){
        $this->_where = $where;
        return $this;
    }

    public function field($field){
        $this->_field = $field;
        return $this;
    }

    public function order($order){
        $this->_order = $order;
        return $this;
    }

    public function limit($limit){
        $this->_limit = $limit;
        return $this;
    }

    public function select($key = ''){
        if(empty($this->_table)){
            $this->_destroy();
            return array();
        }
        $wheres = [];
        if(!empty($this->_where)){
            $wheres = self::_handleWheres($this->_where);
            if(empty($wheres)){
                $this->_destroy();
                return array();
            }
        }

        $fields = self::_handleFields($this->_field);

        if (!empty($this->_order)) {
            $wheres['ORDER'] = $this->_order;
        }

        if (!empty($this->_limit)) {
            $wheres['LIMIT'] = $this->_limit;
        }
        if($this->_join){
            $result = $this->databases->select($this->_table, $this->_join, $fields, $wheres);
        } else {
            $result = $this->databases->select($this->_table, $fields, $wheres);
        }
        $this->_destroy();
        if(empty($result)){
            return array();
        }
        if(empty($key) || is_array($key)){

            return $result;
        }
        return $result;
    }

    public function page($key = '', $count = 'id'){
        if(empty($this->_table)){
            $this->_destroy();
            return self::$_pages;
        }

        $wheres = [];
        if(!empty($this->_where)){
            $wheres = self::_handleWheres($this->_where);
            if($wheres === false){
                $this->_destroy();
                return self::$_pages;
            }
        }

        $fields = self::_handleFields($this->_field);

        if (!empty($this->_order)) {
            $wheres['ORDER'] = $this->_order;
        }

        if (!empty($this->_limit)) {
            $wheres['LIMIT'] = $this->_limit;
        }

        if($this->_join){
            $total = $this->databases->count($this->_table, $this->_join, $count, $wheres);
        } else {
            $total = $this->databases->count($this->_table, $wheres);
        }
        if(empty($total)){
            $this->_destroy();
            return self::$_pages;
        }
        if($this->_join){
            $result = $this->databases->select($this->_table, $this->_join, $fields, $wheres);
        } else {
            $result = $this->databases->select($this->_table, $fields, $wheres);
        }
        $this->_destroy();
        if(empty($result)){
            return self::$_pages;
        }

        if(!empty($key) && !is_array($key)){
            $result = Tool::arrayReduce($result, $key);
        }
        self::$_pages['total'] = $total;

        self::$_pages['rows'] = $result;

        return self::$_pages;
    }

    public function update($update){
        if(empty($this->_table)){
            $this->_destroy();
            return array();
        }
        $wheres = [];
        if(!empty($this->_where)){
            $wheres = self::_handleWheres($this->_where);
            if(empty($wheres)){
                $this->_destroy();
                return array();
            }
        }
        $res = $this->databases->update($this->_table, $update, $wheres);
        $this->_destroy();
        $res = $res->rowCount();
        if($res === false){
            return false;
        }
        return true;
    }

    public function row(){
        if(empty($this->_table)){
            $this->_destroy();
            return array();
        }
        $wheres = [];
        if(!empty($this->_where)){
            $wheres = self::_handleWheres($this->_where);
            if($wheres === false){
                $this->_destroy();
                return array();
            }
        }
        $fields = self::_handleFields($this->_field);
        if (!empty($this->_order)) {
            $wheres['ORDER'] = $this->_order;
        }
        if (!empty($this->_limit)) {
            $wheres['LIMIT'] = $this->_limit;
        }
        if($this->_join){
            $result = $this->databases->get($this->_table, $this->_join, $fields, $wheres);
        } else {
            $result = $this->databases->get($this->_table, $fields, $wheres);
        }
        $this->_destroy();
        if(empty($result)){
            return array();
        }
        return $result;
    }

    public function insert($insert){
        if(empty($this->_table)){
            $this->_destroy();
            return false;
        }
        if(empty($insert)){
            $this->_destroy();
            return false;
        }
        $insert['create_time'] = SYS_TIME;

        $this->databases->insert($this->_table, $insert);
        $this->_destroy();
        return $this->databases->pdo->lastInsertId();
    }

    public function delete(){
        if(empty($this->_table)){
            $this->_destroy();
            return array();
        }
        $wheres = [];
        if(!empty($this->_where)){
            $wheres = self::_handleWheres($this->_where);
            if($wheres === false){
                $this->_destroy();
                return false;
            }
        }
        $res = $this->databases->delete($this->_table,$wheres);
        $res = $res->rowCount();
        $this->_destroy();
        if($res === false){
            return false;
        }
        return true;
    }

    public function exist(){
        if(empty($this->_table)){
            $this->_destroy();
            return array();
        }
        $wheres = [];
        if(!empty($this->_where)){
            $wheres = self::_handleWheres($this->_where);
            if($wheres === false){
                $this->_destroy();
                return array();
            }
        }
        if (!empty($this->_order)) {
            $wheres['ORDER'] = $this->_order;
        }
        $res = $this->databases->has($this->_table, $wheres);
        $this->_destroy();
        return $res;
    }

    protected function _destroy(){
        $this->_table = $this->_field = $this->_order = $this->_limit = $this->_join = '';
        $this->_order = $this->_limit = $this->_join = $this->_where = [];
    }

    public  function selects($tables, $condition = [], $fields = '*', $orders = [], $limits = [])
    {
        if(empty($tables)){
            return false;
        }
        $wheres = [];
        if(!empty($condition)){
            $wheres = self::_handleWheres($condition);
            if($wheres === false){
                return false;
            }
        }
        $fields = self::_handleFields($fields);

        if (!empty($orders)) {
            $wheres['ORDER'] = $orders;
        }

        if (!empty($limits)) {
            $wheres['LIMIT'] = $limits;
        }
        return $this->databases->select($tables, $fields, $wheres);
    }

    public function randSelect($tables, $condition = [], $fields = '*', $orders = [], $limits = []){
        if(empty($tables)){
            return false;
        }
        $wheres = [];
        if(!empty($condition)){
            $wheres = self::_handleWheres($condition);
            if($wheres === false){
                return false;
            }
        }
        $fields = self::_handleFields($fields);

        if (!empty($orders)) {
            $wheres['ORDER'] = $orders;
        }

        if (!empty($limits)) {
            $wheres['LIMIT'] = $limits;
        }
        return $this->databases->rand($tables, $fields, $wheres);
    }

    public function query($sql){
        if(empty($sql) ){
            return false;
        }
        return $this->databases->query($sql);
    }

    public function get($tables, $wheres, $fields = '*', $orders = []){
        if(empty($tables) || empty($wheres)){
            return false;
        }
        $wheres = self::_handleWheres($wheres);
        if($wheres === false){
            return false;
        }
        $fields = self::_handleFields($fields);

        if (!empty($orders)) {
            $wheres['ORDER'] = $orders;
        }
        return $this->databases->get($tables, $fields, $wheres);
    }

    public function updates($tables, $wheres, $updates){
        if(empty($tables) || empty($wheres) || empty($updates)){
            return false;
        }
        $wheres = self::_handleWheres($wheres);
        if($wheres === false){
            return false;
        }
        $res = $this->databases->update($tables, $updates, $wheres);
        $res = $res->rowCount();
        if($res === false){
            return false;
        }
        return true;
    }

    public function removes($tables, $where){
        if(empty($tables) || empty($where)){
            return false;
        }
        $res = $this->databases->delete($tables, $where);
        $res = $res->rowCount();
        if($res === false || empty($res)){
            return false;
        }
        return true;
    }

    public function inserts($tables, $inserts){
        if(empty($tables) || empty($inserts)){
            return false;
        }
        $this->databases->insert($tables, $inserts);
        return $this->databases->pdo->lastInsertId();
    }

    public function has($tables, $wheres){
        if(empty($tables) || empty($wheres)){
            return false;
        }
        $wheres = self::_handleWheres($wheres);
        if(empty($wheres)){
            return false;
        }
        return $this->databases->has($tables, $wheres);
    }

    public function counts($tables, $wheres){
        if(empty($tables)){
            return false;
        }
        return $this->databases->count($tables, $wheres);
    }

    public function sums($tables, $wheres, $field){
        if(empty($tables)){
            return false;
        }
        return $this->databases->sum($tables, $field, $wheres);
    }

    public static function _handleFields($fields){

        if($fields == '*'){
            return '*';
        }

        if(empty($fields)){
            return '*';
        }

        if(is_array($fields)){
            return $fields;
        }

        return array_unique(array_filter(explode(',', $fields)));
    }

    public static function _handleWheres($condition){
        if(empty($condition)){
            return false;
        }

        $where['AND'] = [];
        foreach ($condition as $key => $val){
            if($key == 'OR' && is_array($val)){
                foreach ($val as $or_key => $or_val){
                    $where['AND']['OR'][$or_key] = $or_val;
                }
            }else{
                $where['AND'][$key] = $val;
            }
        }
        if(empty($where['AND'])){
            return false;
        }
        return $where;
    }

    public function database(){
        return $this->databases;
    }

   public function lastSql(){
       return $this->databases->last();
   }

   public function transaction (){
       return $this->databases->pdo->beginTransaction();
   }

    public function commit (){
        return $this->databases->pdo->commit();
    }

    public function rollBack (){
        return $this->databases->pdo->rollBack();
    }



}
