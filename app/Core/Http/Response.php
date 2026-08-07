<?php
namespace Neo\Core\Http;
class Response
{
    public function json($d, $code = 200)
    {
        http_response_code($code);
        header("Content-Type: application/json");
        echo json_encode($d);
        exit();
    }
    public function redirect($u)
    {
        header("Location: " . $u);
        exit();
    }
    public function back()
    {
        $this->redirect($_SERVER["HTTP_REFERER"] ?? "/");
    }
    public function header($k, $v)
    {
        header("$k: $v");
        return $this;
    }
}
