<?php
namespace Neo\Core;
use Medoo\Medoo;
class Database
{
    protected $c;
    public function __construct()
    {
        if (isset($_ENV["DB_DATABASE"]) && $_ENV["DB_DATABASE"]) {
            $this->c = new Medoo([
                "type" => "mysql",
                "host" => $_ENV["DB_HOST"],
                "database" => $_ENV["DB_DATABASE"],
                "username" => $_ENV["DB_USERNAME"],
                "password" => $_ENV["DB_PASSWORD"],
            ]);
        }
    }
    public function __call($n, $a)
    {
        return $this->c ? call_user_func_array([$this->c, $n], $a) : null;
    }

    public function __get($name)
    {
        return $this->c ? $this->c->$name : null;
    }
}
