<?php
namespace App\Core;

/** Validates evidence uploads before they enter persistent storage. */
class UploadValidator
{
    public static function validate(array $file, array $allowedExtensions, int $maxBytes): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            return [false, '檔案上傳未完成或來源無效。'];
        }
        if (($file['size'] ?? 0) < 1 || $file['size'] > $maxBytes) {
            return [false, '佐證檔案大小不符合限制。'];
        }

        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return [false, '不支援的佐證檔案格式。'];
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $validMimes = [
            'pdf' => ['application/pdf'],
            'png' => ['image/png'],
            'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
            'xlsx' => ['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'docx' => ['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ];
        if (!in_array($mime, $validMimes[$extension] ?? [], true)) {
            return [false, '檔案內容與副檔名不符。'];
        }
        if (in_array($extension, ['png', 'jpg', 'jpeg'], true) && @getimagesize($file['tmp_name']) === false) {
            return [false, '圖片檔案內容無效。'];
        }
        if ($extension === 'pdf' && file_get_contents($file['tmp_name'], false, null, 0, 5) !== '%PDF-') {
            return [false, 'PDF 檔案內容無效。'];
        }
        if (in_array($extension, ['xlsx', 'docx'], true)) {
            if (!class_exists(\ZipArchive::class)) return [false, '伺服器未啟用 Office 檔案安全檢查功能。'];
            $zip = new \ZipArchive();
            if ($zip->open($file['tmp_name']) !== true) return [false, 'Office 檔案內容無效。'];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = strtolower((string)$zip->getNameIndex($i));
                if (str_contains($name, 'vbaproject.bin') || str_ends_with($name, '.exe') || str_ends_with($name, '.js')) {
                    $zip->close();
                    return [false, 'Office 檔案含不允許的可執行內容。'];
                }
            }
            $zip->close();
        }
        return [true, $extension];
    }
}
