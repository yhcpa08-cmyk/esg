<?php
namespace App\Core;

/**
 * 視圖渲染器 (View Renderer)
 */
class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void
    {
        // 匯出變數供視圖使用
        extract($data);
        
        $viewsDir = dirname(__DIR__, 2) . '/views/';
        $templatePath = $viewsDir . $template . '.php';

        if (!file_exists($templatePath)) {
            die("視圖模板未找到: " . htmlspecialchars($template, ENT_QUOTES, 'UTF-8'));
        }

        // 開啟輸出緩衝區
        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutPath = $viewsDir . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
                return;
            }
        }

        echo $content;
    }

    public static function e($str): string
    {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
