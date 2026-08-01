<?php
// Chuẩn hóa URL nội bộ khi website được chạy trong thư mục con
// (ví dụ: http://localhost/webgoc2026/) hoặc ở domain gốc.
if (!defined('WEBGOC_BASE_PATH')) {
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(__DIR__);
    $basePath = '';

    if ($documentRoot !== false && $projectRoot !== false) {
        $normalizedDocumentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $normalizedProjectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');

        if (strncasecmp(
            $normalizedProjectRoot,
            $normalizedDocumentRoot,
            strlen($normalizedDocumentRoot)
        ) === 0) {
            $relativeProjectPath = substr(
                $normalizedProjectRoot,
                strlen($normalizedDocumentRoot)
            );
            $basePath = '/' . trim($relativeProjectPath, '/');
            $basePath = $basePath === '/' ? '' : $basePath;
        }
    }

    define('WEBGOC_BASE_PATH', $basePath);
}

if (!function_exists('webgoc_url')) {
    function webgoc_url(string $path = ''): string
    {
        $path = ltrim($path, '/');

        if ($path === '') {
            return WEBGOC_BASE_PATH . '/';
        }

        return WEBGOC_BASE_PATH . '/' . $path;
    }
}

// Hỗ trợ mã nguồn cũ đang dùng đường dẫn tuyệt đối từ domain gốc.
// Chỉ thay các thuộc tính URL trong HTML, không đụng tới URL bên ngoài.
if (
    PHP_SAPI !== 'cli'
    && WEBGOC_BASE_PATH !== ''
    && empty($GLOBALS['webgoc_url_rewriter_started'])
) {
    $GLOBALS['webgoc_url_rewriter_started'] = true;
    $basePath = WEBGOC_BASE_PATH;
    $basePrefix = WEBGOC_BASE_PATH . '/';

    ob_start(static function (string $html) use ($basePath, $basePrefix): string {
        $html = preg_replace_callback(
            '~(\b(?:src|href|action|poster)\s*=\s*)(["\'])(/[^"\']*)\2~i',
            static function (array $matches) use ($basePath, $basePrefix): string {
                $reference = $matches[3];

                if (
                    (
                        str_starts_with($reference, '//')
                        && !preg_match(
                            '~^//(?:images|view|assets|down|app|api)/~i',
                            $reference
                        )
                    )
                    || $reference === $basePath
                    || str_starts_with($reference, $basePrefix)
                ) {
                    return $matches[0];
                }

                return $matches[1]
                    . $matches[2]
                    . $basePrefix
                    . ltrim($reference, '/')
                    . $matches[2];
            },
            $html
        );

        // Trang trong app/ có một số asset viết tương đối như images/...
        // Chuyển riêng asset về gốc dự án, không đổi form action tương đối.
        $html = preg_replace_callback(
            '~(\b(?:src|href|poster)\s*=\s*)(["\'])((?:(?:\.\./)+)?(?:images|view|assets|down|app|api)/[^"\']*|favicon(?:\.ico|-[^"\']+))\2~i',
            static function (array $matches) use ($basePrefix): string {
                $reference = preg_replace('~^(?:\.\./)+~', '', $matches[3]);

                return $matches[1]
                    . $matches[2]
                    . $basePrefix
                    . ltrim($reference, '/')
                    . $matches[2];
            },
            $html
        );

        // Bộ source chỉ có favicon PNG 32x32; dùng file này cho các size bị thiếu.
        $html = preg_replace(
            '~([\'"])' . preg_quote($basePrefix, '~')
                . 'images/favicon-(?:48x48|64x64|128x128)\.png(?:\?[^\'"]*)?\1~i',
            '$1' . $basePrefix . 'images/favicon-32x32.png$1',
            $html
        );

        // Chuẩn hóa URL nội bộ nằm trong JavaScript/AJAX nhúng trên trang.
        $html = preg_replace_callback(
            '~(["\'])/(app|api|view|images|down)/~i',
            static function (array $matches) use ($basePrefix): string {
                return $matches[1] . $basePrefix . $matches[2] . '/';
            },
            $html
        );

        // Xóa loader Cloudflare còn sót lại trong source tải về; file này không
        // tồn tại trên localhost và các script thường đã được nạp trực tiếp.
        return preg_replace(
            '~<script\b[^>]*src=["\'][^"\']*/cdn-cgi/[^"\']*rocket-loader[^"\']*["\'][^>]*>\s*</script>~i',
            '',
            $html
        );
    });
}
