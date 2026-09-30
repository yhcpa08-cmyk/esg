<?php
namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Auth;
use App\Core\View;
use App\Services\AiAssistantService;

class AiAssistantController
{
    public function chat(): void
    {
        if (!Auth::check()) { http_response_code(401); $this->json(['error' => '登入已失效，請重新登入。']); return; }
        header('Content-Type: application/json; charset=utf-8');
        if (!empty($_SESSION['ai_assistant_disconnected'])) { $this->json(['disconnected' => true, 'answer' => 'AI 客服已因 3 次範圍外提問而中斷。請重新登入後再使用。']); return; }
        $securityLockUntil = (int)($_SESSION['ai_assistant_security_lock_until'] ?? 0);
        if ($securityLockUntil > time()) { $this->json(AiAssistantService::securityLockResponse($securityLockUntil)); return; }
        unset($_SESSION['ai_assistant_security_lock_until']);
        $payload = json_decode(file_get_contents('php://input'), true);
        $question = trim((string)($payload['message'] ?? ''));
        if (AiAssistantService::isSecurityQuestion($question)) {
            $lockedUntil = time() + 180;
            $_SESSION['ai_assistant_security_lock_until'] = $lockedUntil;
            AuditLogger::log('AI_ASSISTANT', 'SECURITY_LOCK', null, null, ['user_id' => (int)Auth::user()['id'], 'lock_seconds' => 180]);
            $this->json(AiAssistantService::securityLockResponse($lockedUntil));
            return;
        }
        if (!AiAssistantService::isInScope($question)) {
            $warnings = min(3, (int)($_SESSION['ai_assistant_warnings'] ?? 0) + 1);
            $_SESSION['ai_assistant_warnings'] = $warnings;
            if ($warnings >= 3) $_SESSION['ai_assistant_disconnected'] = true;
            $this->json(AiAssistantService::warningResponse($warnings));
            return;
        }
        try {
            $history = is_array($_SESSION['ai_assistant_history'] ?? null) ? $_SESSION['ai_assistant_history'] : [];
            $answer = AiAssistantService::answer($question, $history);
            $history[] = ['role' => 'user', 'content' => $question];
            $history[] = ['role' => 'assistant', 'content' => $answer];
            $_SESSION['ai_assistant_history'] = array_slice($history, -8);
            AuditLogger::log('AI_ASSISTANT', 'CHAT', null, null, ['user_id' => (int)Auth::user()['id'], 'question_length' => mb_strlen($question)]);
            $this->json(['answer' => $answer, 'warning_count' => (int)($_SESSION['ai_assistant_warnings'] ?? 0), 'disconnected' => false]);
        } catch (\Throwable $e) {
            http_response_code(503);
            // Technical details stay server-side; end users receive a safe, actionable message.
            error_log('AI assistant error: ' . $e->getMessage());
            $this->json(['error' => 'AI 客服暫時無法使用，請稍後再試或聯絡系統管理員確認 API 設定。']);
        }
    }

    public function settings(): void
    {
        if (!Auth::isSuperAdmin()) { http_response_code(403); die('403 Forbidden: 僅系統管理員可設定 AI 客服。'); }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                AiAssistantService::save((string)($_POST['model'] ?? ''), trim((string)($_POST['api_key'] ?? '')) ?: null, isset($_POST['is_enabled']), (int)Auth::user()['id']);
                AuditLogger::log('AI_ASSISTANT_SETTINGS', 'UPDATE', null, null, ['model' => $_POST['model'] ?? '', 'enabled' => isset($_POST['is_enabled'])]);
                header('Location: ' . Auth::baseUrl() . '/admin/ai-assistant?msg=saved'); exit;
            } catch (\Throwable $e) { $error = $e->getMessage(); }
        }
        View::render('admin/ai_assistant', ['settings' => AiAssistantService::settings(), 'models' => AiAssistantService::MODELS, 'error' => $error]);
    }

    public function testConnection(): void
    {
        if (!Auth::isSuperAdmin()) { http_response_code(403); die('403 Forbidden: 僅系統管理員可測試 AI 客服連線。'); }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $message = AiAssistantService::testConnection();
            AuditLogger::log('AI_ASSISTANT_SETTINGS', 'TEST', null, null, ['result' => 'success']);
            $this->json(['success' => true, 'message' => '連線成功：' . $message]);
        } catch (\Throwable $e) {
            AuditLogger::log('AI_ASSISTANT_SETTINGS', 'TEST', null, null, ['result' => 'failed']);
            http_response_code(422); $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function json(array $data): void { echo json_encode($data, JSON_UNESCAPED_UNICODE); }
}
