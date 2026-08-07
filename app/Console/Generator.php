<?php
namespace Neo\Console;
class Generator
{
    public function makeController($n)
    {
        $p = __DIR__ . "/../../app/Controllers/" . $n . ".php";
        @mkdir(dirname($p), 0755, true);
        file_put_contents(
            $p,
            "<?php namespace App\Controllers; class $n { public function index(){ echo 'Hello'; } }",
        );
        echo "Created Controller $n\n";
    }
    public function makeModel($n)
    {
        $p = __DIR__ . "/../../app/Models/" . $n . ".php";
        @mkdir(dirname($p), 0755, true);
        file_put_contents(
            $p,
            "<?php namespace App\Models; use Neo\Core\Model; class $n extends Model { protected \$table='" .
            strtolower($n) .
            "s'; }",
        );
        echo "Created Model $n\n";
    }

    public function makePlugin($n)
    {
        $d = __DIR__ . "/../../plugins/" . $n;
        @mkdir($d, 0755, true);

        // 1. Create plugin.json
        $meta = [
            'name' => $n,
            'version' => '1.0.0',
            'entry' => "Plugins\\$n\\Plugin",
            'file' => 'Plugin.php'
        ];
        file_put_contents($d . '/plugin.json', json_encode($meta, JSON_PRETTY_PRINT));

        // 2. Create Plugin.php
        $content = "<?php namespace Plugins\\$n;\n\n";
        $content .= "class Plugin {\n";
        $content .= "    protected \$app;\n\n";
        $content .= "    public function __construct(\$app) {\n";
        $content .= "        \$this->app = \$app;\n";
        $content .= "    }\n\n";
        $content .= "    public function register() {\n";
        $content .= "        // Register Services\n";
        $content .= "    }\n\n";
        $content .= "    public function boot() {\n";
        $content .= "        // Boot logic (Routes, Events)\n";
        $content .= "        \$this->app->router('/" . strtolower($n) . "', 'GET', function() {\n";
        $content .= "            return 'Hello from $n Plugin';\n";
        $content .= "        });\n";
        $content .= "    }\n";
        $content .= "}\n";

        file_put_contents($d . '/Plugin.php', $content);

        echo "Created Plugin $n at plugins/$n\n";
    }
}
