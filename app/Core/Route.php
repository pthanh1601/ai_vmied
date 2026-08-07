<?php namespace Neo\Core;
class Route
{
    public $path,
        $method,
        $callback,
        $middlewares = [];
    public function __construct($p, $m, $c)
    {
        $this->path = $p;
        $this->method = strtoupper($m);
        $this->callback = $c;
    }
    public function middleware($m)
    {
        if (is_array($m)) {
            $this->middlewares = array_merge($this->middlewares, $m);
        } else {
            $this->middlewares[] = $m;
        }
        return $this;
    }
}
