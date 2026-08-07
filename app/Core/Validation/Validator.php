<?php 
namespace Neo\Core\Validation;

class Validator
{
    protected $errors = [];
    protected $data;
    protected $attributes = [];
    protected $customMessages = [];
    protected $defaultMessages = [
        'required' => ':attribute không được để trống.',
        'email'    => ':attribute không hợp lệ.',
        'min'      => ':attribute tối thiểu :min ký tự.',
        'unique'   => ':attribute đã tồn tại trên hệ thống.'
    ];

    public function make($data, $rules, $messages = [], $attributes = [])
    {
        $this->data = $data;
        $this->customMessages = $messages;
        $this->attributes = $attributes;
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            foreach (explode('|', $ruleString) as $ruleItem) {
                [$ruleName, $params] = $this->parseRule($ruleItem);
                $method = 'v' . ucfirst($ruleName);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $params)) break;
                }
            }
        }
        return $this;
    }

    private function parseRule($rule) {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule);
            $params = explode(',', $paramStr);
        }
        return [$rule, $params];
    }

    // ==========================================
    // CÁC KIỂU TRẢ KẾT QUẢ (OUTPUT METHODS)
    // ==========================================

    /**
     * Kiểm tra có lỗi hay không
     */
    public function fails() {
        return !empty($this->errors);
    }

    /**
     * Trả về toàn bộ mảng lỗi (Dạng: ['email' => '...', 'name' => '...'])
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Lấy lỗi đầu tiên của bất kỳ field nào (Dạng: string)
     * Thích hợp cho thông báo Alert chung trên Alpine.js
     */
    public function first() {
        return $this->fails() ? reset($this->errors) : null;
    }

    /**
     * Lấy lỗi của một field cụ thể
     */
    public function get($field) {
        return $this->errors[$field] ?? null;
    }

    /**
     * Trả về JSON và dừng chương trình (Dành cho API/AJAX nhanh)
     */
    public function throwJson($status = 'error') {
        if ($this->fails()) {
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => $status,
                'content' => $this->first(), // Hoặc $this->errors() nếu muốn trả hết
                'all'     => $this->errors()
            ]);
            exit;
        }
    }

    /**
     * Trả về HTML (Dành cho HTMX nếu muốn render thẳng một thông báo)
     */
    public function throwHtml() {
        if ($this->fails()) {
            echo "<div class='alert alert-danger'>{$this->first()}</div>";
            exit;
        }
    }

    // ==========================================
    // LOGIC KIỂM TRA (RULES)
    // ==========================================

    protected function addError($field, $rule, $replace = []) {
        $msg = $this->customMessages["$field.$rule"] ?? ($this->defaultMessages[$rule] ?? "$field invalid");
        $attr = $this->attributes[$field] ?? $field;
        $msg = str_replace(':attribute', $attr, $msg);
        foreach ($replace as $k => $v) $msg = str_replace(":$k", $v, $msg);
        $this->errors[$field] = $msg;
    }

    protected function vRequired($f) {
        if (empty($this->data[$f])) { $this->addError($f, 'required'); return false; }
        return true;
    }

    protected function vEmail($f) {
        if (!empty($this->data[$f]) && !filter_var($this->data[$f], FILTER_VALIDATE_EMAIL)) {
            $this->addError($f, 'email'); return false;
        }
        return true;
    }

    protected function vMin($f, $p) {
        if (strlen($this->data[$f] ?? '') < $p[0]) {
            $this->addError($f, 'min', ['min' => $p[0]]); return false;
        }
        return true;
    }
}