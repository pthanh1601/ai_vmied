<?php
namespace Neo\Core;

abstract class Model {
    protected $table;
    protected $primaryKey = 'id';
    
    // Tự động đoán tên bảng nếu không khai báo (User -> users)
    public function getTable() {
        if ($this->table) return $this->table;
        $class = (new \ReflectionClass($this))->getShortName();
        return strtolower($class) . 's';
    }

    public static function query() {
        return app()->db; // Trả về Medoo instance
    }

    public static function all() {
        $instance = new static;
        return self::query()->select($instance->getTable(), '*');
    }

    public static function find($id) {
        $instance = new static;
        return self::query()->get($instance->getTable(), '*', [$instance->primaryKey => $id]);
    }

    public static function create($data) {
        $instance = new static;
        self::query()->insert($instance->getTable(), $data);
        return self::query()->id();
    }
    
    public static function where($column, $value) {
        $instance = new static;
        return self::query()->select($instance->getTable(), '*', [$column => $value]);
    }
}