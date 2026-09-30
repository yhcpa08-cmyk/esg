<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\AuditLogger;

class TaskController
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
    }

    public function index(): void
    {
        $user = Auth::user();
        $status = $_GET['status'] ?? '';

        $sql = "
            SELECT t.*, u1.real_name as assigned_name, u2.real_name as approver_name
            FROM esg_tasks t
            JOIN sys_users u1 ON t.assigned_to = u1.id
            JOIN sys_users u2 ON t.approver_id = u2.id
            WHERE 1=1
        ";
        $params = [];

        // 若不是管理員或永續小組，則只能看分派給自己或待自己審核的工單
        if (!Auth::hasRole('ROLE_ADMIN', 'ROLE_CSO', 'ROLE_ESG_MGR')) {
            $sql .= " AND (t.assigned_to = ? OR t.approver_id = ?)";
            $params[] = $user['id'];
            $params[] = $user['id'];
        }

        if ($status) {
            $sql .= " AND t.current_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY t.task_id DESC";

        $tasks = Database::query($sql, $params);
        $users = Database::query("SELECT id, real_name, role_id FROM sys_users WHERE status = 'ACTIVE' ORDER BY id ASC");

        View::render('tasks/index', [
            'tasks'  => $tasks,
            'users'  => $users,
            'status' => $status
        ]);
    }

    public function create(): void
    {
        Auth::requireRoles('ROLE_CSO', 'ROLE_ESG_MGR');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['task_title'] ?? '');
            $module = $_POST['module_type'] ?? 'ENV';
            $assignedTo = (int)($_POST['assigned_to'] ?? 0);
            $approverId = (int)($_POST['approver_id'] ?? 0);
            $dueDate = $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'));

            $taskId = Database::insert(
                "INSERT INTO esg_tasks (task_title, module_type, assigned_to, approver_id, due_date, current_status)
                 VALUES (?, ?, ?, ?, ?, 'PENDING')",
                [$title, $module, $assignedTo, $approverId, $dueDate]
            );

            AuditLogger::log('TASK_WORKFLOW', 'INSERT', $taskId, null, ['title' => $title, 'assigned_to' => $assignedTo]);

            header('Location: ' . Auth::baseUrl() . '/tasks?msg=dispatched');
            exit;
        }
    }

    public function review(): void
    {
        Auth::requireRoles('ROLE_CSO', 'ROLE_DEPT_MGR');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = (int)($_POST['task_id'] ?? 0);
            $action = $_POST['action'] ?? ''; // 'APPROVE' or 'REJECT'
            $comment = trim($_POST['approval_comment'] ?? '');

            $task = Database::fetch("SELECT * FROM esg_tasks WHERE task_id = ?", [$taskId]);
            if ($task) {
                $user = Auth::user();
                if (!Auth::isSuperAdmin() && ($user['role_code'] ?? '') !== 'ROLE_CSO' && (int)$task['approver_id'] !== (int)$user['id']) {
                    http_response_code(403);
                    die('403 Forbidden: 僅被指派的簽核人可審核此任務。');
                }
                if (!in_array($task['current_status'], ['PENDING', 'UNDER_REVIEW'], true)) {
                    http_response_code(409);
                    die('409 Conflict: 此任務已完成審核。');
                }
                $newStatus = ($action === 'APPROVE') ? 'APPROVED' : 'REJECTED';
                Database::execute(
                    "UPDATE esg_tasks 
                     SET current_status = ?, approval_comment = ?, signed_at = NOW()
                     WHERE task_id = ?",
                    [$newStatus, $comment, $taskId]
                );
                if ($task['module_type'] === 'ENV') {
                    Database::execute('UPDATE esg_ghg_records SET data_status = ? WHERE task_id = ? AND data_status = \'SUBMITTED\'', [$newStatus, $taskId]);
                }

                AuditLogger::log('TASK_WORKFLOW', 'UPDATE', $taskId, $task, [
                    'status'  => $newStatus,
                    'comment' => $comment
                ]);
            }

            header('Location: ' . Auth::baseUrl() . '/tasks?msg=reviewed');
            exit;
        }
    }
}
