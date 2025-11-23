<?php
header('Content-Type: application/json; charset=utf-8');
include '../../config/db.php';

try {
    // Read filters from POST (AJAX)
    $search = trim($_POST['search'] ?? '');
    $role = $_POST['role'] ?? 'all';
    $status = $_POST['status'] ?? 'all';
    $page = max(1, (int)($_POST['page'] ?? 1));
    $perPage = (int)($_POST['per_page'] ?? 20);

    $where = "WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $where .= " AND (fullname LIKE ? OR username LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if ($role !== 'all') {
        $where .= " AND role = ?";
        $params[] = $role;
    }

    if ($status !== 'all') {
        $where .= " AND status = ?";
        $params[] = $status;
    }

    // Total count
    $totalRow = db_fetch("SELECT COUNT(*) AS cnt FROM users {$where}", $params);
    $total = intval($totalRow['cnt'] ?? 0);

    // Pagination
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM users {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $fetchParams = array_merge($params, [$perPage, $offset]);
    $users = db_fetch_all($sql, $fetchParams);

    // Stats
    $activeRow = db_fetch("SELECT COUNT(*) AS cnt FROM users WHERE status = ?", ['Active']);
    $active = intval($activeRow['cnt'] ?? 0);
    $adminRow = db_fetch("SELECT COUNT(*) AS cnt FROM users WHERE role = ?", ['Admin']);
    $admin = intval($adminRow['cnt'] ?? 0);
    $cashierRow = db_fetch("SELECT COUNT(*) AS cnt FROM users WHERE role = ?", ['Cashier']);
    $cashier = intval($cashierRow['cnt'] ?? 0);

    echo json_encode([
        'success' => true,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'users' => $users,
        'stats' => [
            'total' => $total,
            'active' => $active,
            'admin' => $admin,
            'cashier' => $cashier,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
