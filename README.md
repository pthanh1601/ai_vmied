# Neo Framework 🚀

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)
![Status](https://img.shields.io/badge/status-stable-green.svg)

**Neo Framework** là một PHP Micro-Framework hiệu năng cao.

Nó cung cấp đầy đủ các tính năng hiện đại cần thiết cho phát triển web như Middleware, ORM, Modular Plugins, Caching và CLI Tool nhưng vẫn giữ được tốc độ khởi động cực nhanh và không cồng kềnh.

---

## 📑 Mục Lục

- [Tính Năng Nổi Bật](#-tính-năng-nổi-bật)
- [Yêu Cầu Hệ Thống](#-yêu-cầu-hệ-thống)
- [Cài Đặt](#-cài-đặt)
- [Cấu Trúc Thư Mục](#-cấu-trúc-thư-mục)
- [Định Tuyến (Routing)](#-định-tuyến-routing)
- [Controllers & Request](#-controllers--request)
- [Views & Template Engine](#-views--template-engine)
- [Cơ Sở Dữ Liệu & Models](#-cơ-sở-dữ-liệu--models)
- [Hệ Thống Plugins](#-hệ-thống-plugins)
- [CLI Tool](#-cli-tool)

---

## ✨ Tính Năng Nổi Bật

- **🚀 Siêu nhẹ:** Khởi động cực nhanh, không bloatware.
- **🛡️ Bảo mật:** Tích hợp sẵn CSRF Protection, Input Validation tự động, XSS Protection.
- **🔌 Modular:** Hệ thống Plugin dựa trên thư mục, mỗi plugin là một module độc lập.
- **🎨 Smart View:** Template engine hỗ trợ kế thừa (`extend`, `section`), tự động nén HTML, hỗ trợ AJAX rendering thông minh (tự loại bỏ layout khi gọi AJAX).
- **💾 Caching:** Hệ thống Cache file đơn giản nhưng hiệu quả.
- **🌍 i18n:** Hỗ trợ đa ngôn ngữ dễ dàng.
- **🛠️ CLI:** Công cụ dòng lệnh `neo` để tạo code tự động.
- **🚨 Error Handling:** Trang lỗi 404, 403, 500 đẹp mắt, hỗ trợ debug mode.

---

## 💻 Yêu Cầu Hệ Thống

* PHP >= 8.1
* Composer
* Database: MySQL (Khuyến nghị)
* Server: Apache (có sẵn .htaccess) hoặc Nginx

---

## 📦 Cài Đặt

1. **Clone dự án:**
   ```bash
   git clone [https://github.com/Jatbi/neo-eclo.git](https://github.com/Jatbi/neo-eclo.git)
   cd neo-framework
   ```

2. **Cài đặt Dependencies:**
   ```bash
   composer install
   ```

3. **Cấu hình môi trường:**
   Copy file `.env.example` thành `.env` và cập nhật thông tin Database.
   ```ini
   APP_ENV=local
   APP_DEBUG=true
   APP_MINIFY_HTML=true
   APP_THEME=default
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=neo_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Khởi chạy Server:**
   ```bash
   php neo serve
   ```
   Truy cập: `http://localhost:8000`

---

## 📂 Cấu Trúc Thư Mục

```
neo-framework/
├── app/                  # Core Framework & Logic ứng dụng
│   ├── Controllers/      # Nơi chứa Controller
│   ├── Models/           # Nơi chứa Model (Active Record)
│   ├── Core/             # Mã nguồn lõi (Router, View, Http...)
│   └── Helpers/          # Các hàm tiện ích
├── config/               # Cấu hình hệ thống
├── plugins/              # Các Module mở rộng (Plugins)
├── public/               # Web Root (index.php, css, js)
├── resources/
│   └── lang/             # File ngôn ngữ (en, vi)
├── storage/              # Cache và Logs
├── themes/               # Giao diện (Views)
│   └── default/
│       ├── layouts/      # Layout chính
│       └── errors/       # Trang lỗi tùy chỉnh (404, 500)
└── neo                   # Công cụ CLI
```

---

## 🚦 Định Tuyến (Routing)

Định nghĩa route trong `public/index.php` hoặc file `boot` của Plugin.

### Route cơ bản (Closure)
```php
$app->router('/hello', 'GET', function() {
    return "Hello World";
});
```

### Route đến Controller
```php
use App\Controllers\UserController;
// Gọi method 'profile' trong UserController
$app->router('/user/{id}', 'GET', [UserController::class, 'profile']);
```

### Route Group & Middleware
Nhóm các route để áp dụng chung tiền tố (prefix) hoặc kiểm tra quyền (middleware).

```php
$app->group(['prefix' => '/admin', 'middleware' => 'auth'], function($app) {
    
    // URL: /admin/dashboard
    $app->router('/dashboard', 'GET', function() {
        return view('admin/dashboard');
    });
    
});
```

---

## 🎮 Controllers & Request

Sử dụng CLI để tạo nhanh Controller:
```bash
php neo make:controller HomeController
```

### Xử lý Request
Lấy dữ liệu từ người dùng (POST, GET, JSON) thông qua helper `request()`.

```php
namespace App\Controllers;

class HomeController {
    public function save() {
        // Validation tự động (Redirect back kèm lỗi nếu fail)
        app()->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // Lấy dữ liệu input
        $name = request('name'); 
        $email = request('user.email'); // Dot notation cho mảng
        
        // Kiểm tra AJAX request
        if (app()->request->isAjax()) {
            return response()->json(['status' => 'success']);
        }
        
        return response()->back();
    }
}
```

---

## 🎨 Views & Template Engine

Neo View Engine hỗ trợ kế thừa giao diện (Template Inheritance) sử dụng cú pháp PHP thuần túy.

### Layout Chính (`themes/default/layouts/master.php`)
Sử dụng `$this->yield('content')` để đánh dấu vị trí nội dung con.

```php
<!DOCTYPE html>
<html>
<head><title><?= $title ?? 'Neo App' ?></title></head>
<body>
    <header>Menu</header>
    <main>
        <?= $this->yield('content') ?>
    </main>
    <footer>Footer</footer>
</body>
</html>
```

### View Con (`themes/default/home.php`)
Sử dụng `$this->extend` để kế thừa và `$this->section` để định nghĩa nội dung.

```php
<?php $this->extend('layouts/master') ?>

<?php $this->section('content') ?>
    <h1>Trang Chủ</h1>
    <!-- Gọi Component -->
    <?= $this->component('alert', ['msg' => 'Welcome!']) ?>
<?php $this->endSection() ?>
```

> **Smart Feature:** Nếu request là AJAX, Neo Framework sẽ tự động loại bỏ Layout và chỉ trả về nội dung của View.

---

## 🗄️ Cơ Sở Dữ Liệu & Models

Neo tích hợp **Medoo** và lớp **Model** Active Record.

### Tạo Model
```bash
php neo make:model Product
```

### Sử dụng
```php
use App\Models\Product;

// 1. Lấy tất cả
$items = Product::all();

// 2. Tìm theo ID
$item = Product::find(1);

// 3. Tạo mới
Product::create([
    'name' => 'Laptop',
    'price' => 1000
]);

// 4. Query Builder (Where đơn giản)
$list = Product::where('price', 1000);

// 5. Query Nâng cao (Medoo)
$data = Product::query()->select('products', '*', [
    'price[>]' => 500,
    'LIMIT' => 10
]);
```

---

## ⚡ Events & Service Container

### Event Dispatcher
Neo hỗ trợ cơ chế Events để lắng nghe và can thiệp vào lường xử lý của ứng dụng.

```php
// 1. Lắng nghe sự kiện (Action)
$app->events->listen('app.start', function($app) {
    // Logic chạy khi app bắt đầu
});

// 2. Kích hoạt sự kiện
$app->events->fire('custom.event', $payload);

// 3. Filter (Modify data)
$app->events->listen('the_content', function($content) {
    return $content . " - Verified";
});
$newContent = $app->events->filter('the_content', "Original");
```

### Service Binding
Đăng ký và sử dụng dịch vụ thông qua Container.

```php
// Bind
$app->bind('my_service', function($app) {
    return new MyService();
});

// Resolve
$service = $app->make('my_service');
```

---

## 📧 Gửi Email (PHPMailer)

Neo tích hợp sẵn wrapper cho **PHPMailer**.

### Cấu hình `.env`
```ini
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
```

### Sử dụng
```php
app()->mail
    ->to('user@example.com', 'User Name')
    ->subject('Welcome to Neo')
    ->body('<h1>Hello</h1>', 'Hello plain text')
    ->send();
```

---

## 📁 Upload & Xử lý Ảnh (Intervention Image)

Neo sử dụng **Intervention Image** - Thư viện xử lý ảnh tốt nhất cho PHP.
Wrapper `Neo\Core\Upload` đã tự động xử lý bảo mật (check MIME type, extension) và resize.

### Sử dụng
```php
use Neo\Core\Upload;

// 1. Khởi tạo
$upload = new Upload($_FILES['image']);

// 2. Cấu hình (Hỗ trợ Resize)
$options = [
    'image_resize'       => true,
    'image_x'            => 500,  // Width
    'image_y'            => null, // Height (Auto)
    'image_ratio_y'      => true, // Maintain Aspect Ratio
];

// 3. Lưu và Xử lý
if ($upload->save(app()->basePath() . '/public/uploads', $options)) {
    // Thành công
    $data = $upload->getResult();
    echo $data['name']; // Tên file: img_658a...jpg
} else {
    // Thất bại
    echo $upload->getError();
}
```
> **Lưu ý:** Để sử dụng các tính năng nâng cao khác của Intervention (Filter, Watermark...), bạn có thể chỉnh sửa trực tiếp class `app/Core/Upload.php`.

---

## 🧩 Hệ Thống Plugins

Neo hỗ trợ kiến trúc Module hóa hiện đại với `plugin.json` và Class-based structure.

### Tạo Plugin mới
```bash
php neo make:plugin Shop
```
Lệnh này sẽ tạo cấu trúc: `plugins/Shop/` gồm `plugin.json` và `Plugin.php`.

### Cấu trúc Plugin (`plugins/Shop/Plugin.php`)
```php
namespace Plugins\Shop;

class Plugin {
    protected $app;
    
    public function __construct($app) { $this->app = $app; }

    // Đăng ký Services
    public function register() {
        $this->app->bind('cart', function() { return new Cart(); });
    }

    // Chạy logic chính (Routes, Events)
    public function boot() {
        $this->app->router('/shop', 'GET', function() { return "Shop Home"; });
    }
}
```

---

## 📡 WebSocket Client (Textalk)

Neo tích hợp `textalk/websocket` để đóng vai trò là một WebSocket Client (kết nối đến server socket khác).

### Cấu hình `.env`
```ini
WS_URL=wss://ws.postman-echo.com/raw
```

### Sử dụng
```php
// 1. Gửi tin nhắn
app()->ws->send("Hello Server");

// 2. Nhận phản hồi (Blocking)
$response = app()->ws->receive();
```

---

## 🔔 Web Push Notification (VAPID)

Hỗ trợ gửi thông báo đẩy (Web Push) qua Service Worker sử dụng thư viện `minishlink/web-push`.

### Cấu hình
1. Sinh cặp key VAPID (có thể dùng `https://vapidkeys.com/` hoặc lệnh `openssl`).
2. Cập nhật `.env`:
   ```ini
   VAPID_SUBJECT=mailto:admin@example.com
   VAPID_PUBLIC_KEY=your_public_key
   VAPID_PRIVATE_KEY=your_private_key
   ```
3. Đăng ký Service Worker ở Frontend (`public/sw.js`).

### Sử dụng (Backend)
```php
// Gửi trực tiếp (Đồng bộ - User phải đợi)
$subscription = [ ... ]; // JSON từ client
app()->push->send($subscription, "Hello World from PHP!");
```

---

## ⏳ Async Queue (Background Jobs)

Để tránh việc gửi Email hoặc Push Notification làm chậm trải nghiệm người dùng (do PHP phải đợi phản hồi từ server bên thứ 3), Neo tích hợp hệ thống **Database Queue**.

### Cơ chế hoạt động
1. **Producer**: Controller tạo một "Job" và lưu vào bảng `jobs` trong Database (dùng `$app->queue->push`). Việc này cực nhanh.
2. **Consumer (Worker)**: Một tiến trình CLI chạy ngầm liên tục kiểm tra Database và thực thi các Job này.

### Cấu hình & Chạy Worker
Để Queue hoạt động, bạn **BẮT BUỘC** phải chạy lệnh worker chạy ngầm:

```bash
php neo queue:work
```
*Nên sử dụng Supervisor hoặc Systemd để giữ tiến trình này luôn chạy trên Production.*

### Sử dụng

**1. Tạo Job Class (`app/Jobs/SendMailJob.php`)**
```php
namespace App\Jobs;
class SendMailJob {
    protected $email;
    public function __construct($email) { $this->email = $email; }
    
    public function handle() {
        app()->mail->to($this->email)->subject("Hi")->send();
    }
}
```

**2. Đẩy Job vào Queue (Trong Controller)**
```php
use App\Jobs\SendMailJob;

// Thay vì gửi email ngay, hãy đẩy vào queue
app()->queue->push(new SendMailJob('user@example.com'));

return response()->json(['msg' => 'Email đang được gửi ngầm!']);
```

---

---
 
 ## 🛡️ Bảo mật & API (Security)
 
 ### 1. Rate Limiting (Throttle)
 Giới hạn số lượng request từ một IP để chống spam/DDOS.
 
 **Sử dụng:** Áp dụng Middleware `throttle` cho route.
 ```php
 $app->router('/api/data', 'GET', ...)->middleware('throttle');
 ```
 *Mặc định: 60 requests / phút.*
 
 ### 2. JWT Authentication (API Login)
 Cơ chế đăng nhập không trạng thái (stateless) cho Mobile App / SPA.
 
 **Cài đặt & Cấu hình:**
 1. Cài thư viện: `composer require firebase/php-jwt`
 2. Cấu hình `.env`:
    ```ini
    APP_KEY=chuoi_bi_mat_ngau_nhien_dai_32_ky_tu
    ```
 
 **Sử dụng:**
 
 *   **Login (Tạo Token)**:
     ```php
     use Firebase\JWT\JWT;
     // ... Kiểm tra user/pass
     $payload = [
         'iss' => 'neo',
         'uid' => $user_id,
         'exp' => time() + 3600
     ];
     $token = JWT::encode($payload, $_ENV['APP_KEY'], 'HS256');
     return response()->json(['token' => $token]);
     ```
 
 *   **Bảo vệ Route (Middleware)**:
     ```php
     $app->router('/api/profile', 'GET', ...)->middleware('auth.api');
     ```
     Middleware `auth.api` sẽ tự động kiểm tra Header `Authorization: Bearer <token>` và gán thông tin user vào `app()->request->user`.
 
 --- 
 
 ## 🛠️ CLI Tool

Danh sách các lệnh hỗ trợ:

| Lệnh | Mô tả |
|------|-------|
| `php neo serve` | Khởi chạy server development (localhost:8000) |
| `php neo make:controller {Name}` | Tạo Controller mới |
| `php neo make:model {Name}` | Tạo Model mới |
| `php neo make:plugin {Name}` | Tạo Plugin mới (Structure chuẩn) |
| `php neo build` | Minify CSS & JS trong thư mục public |
| `php neo queue:work` | Chạy Queue Worker để xử lý Background Jobs |

---

### License

Dự án được phát hành dưới giấy phép **MIT**.