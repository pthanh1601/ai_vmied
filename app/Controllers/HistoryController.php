<?php
namespace App\Controllers;

class HistoryController
{
    public function Index()
    {
        $user = app()->request->user;
        return view('account/history', [
            'user' => $user,
        ]);
    }

    public function GetUsageLogs()
    {
        $userId = app()->request->user->uuid;
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Chưa đăng nhập'], 401);
        }

        $limit  = min((int) (request('limit') ?? 20), 100);
        $page   = max((int) (request('page')  ?? 1), 1);
        $offset = ($page - 1) * $limit;
        $type   = request('type') ?? 'all'; 

        $result = [];

        // ---- Lịch sử dùng AI ----
        if ($type === 'all' || $type === 'ai') {
            $toolMap = [
                'plagiarism'  => 'Kiểm tra đạo văn',
                'ai_detector' => 'Kiểm tra nội dung AI',
                'rewriter'    => 'Viết lại nội dung',
                'seo'         => 'Tối ưu SEO',
                'chat'        => 'Chat AI',
            ];

            $logs = app()->db->select('ai_usage_logs',
                ['uuid', 'tool', 'input_words', 'tokens_used', 'points_used', 'status', 'created_at'],
                [
                    'account' => $userId,
                    'ORDER'   => ['created_at' => 'DESC'],
                    'LIMIT'   => $limit,
                    'OFFSET'  => $offset,
                ]
            );

            foreach (($logs ?? []) as $log) {
                $result[] = [
                    'uuid'        => $log['uuid'],
                    'type'        => 'ai',
                    'tool'        => $log['tool'],
                    'tool_name'   => $toolMap[$log['tool']] ?? $log['tool'],
                    'input_words' => (int) $log['input_words'],
                    'tokens_used' => (int) $log['tokens_used'],
                    'points_used' => (int) $log['points_used'],
                    'status'      => $log['status'] ? 'success' : 'danger',
                    'status_text' => $log['status'] ? 'Thành công' : 'Thất bại',
                    'created_at'  => $log['created_at'],
                ];
            }
        }

        // ---- Lịch sử nạp tiền ----
        if ($type === 'all' || $type === 'payment') {
            $methodMap = [
                'qr'   => 'Chuyển khoản QR',
                'momo' => 'Ví Momo',
                'card' => 'Thẻ quốc tế',
            ];

            $statusMap = [
                0 => ['text' => 'Đang xử lý', 'color' => 'warning'],
                1 => ['text' => 'Thành công',  'color' => 'success'],
                2 => ['text' => 'Thất bại',    'color' => 'danger'],
            ];

            $txs = app()->db->select('transactions',
                ['uuid', 'amount', 'vmied', 'method', 'status', 'created_at'],
                [
                    'account' => $userId,
                    'ORDER'   => ['created_at' => 'DESC'],
                    'LIMIT'   => $limit,
                    'OFFSET'  => $offset,
                ]
            );

            foreach (($txs ?? []) as $tx) {
                $s = $statusMap[$tx['status']] ?? $statusMap[0];
                $result[] = [
                    'uuid'        => $tx['uuid'],
                    'type'        => 'payment',
                    'tool'        => 'payment',
                    'tool_name'   => 'Nạp VMIED',
                    'method'      => $tx['method'],
                    'method_name' => $methodMap[$tx['method']] ?? $tx['method'],
                    'amount'      => (int) $tx['amount'],
                    'vmied'       => (int) $tx['vmied'],
                    'points_used' => 0,
                    'status'      => $s['color'],
                    'status_text' => $s['text'],
                    'created_at'  => $tx['created_at'],
                ];
            }

            if ($type === 'all') {
                usort($result, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                $result = array_slice($result, 0, $limit);
            }
        }

        $totalAi      = ($type === 'all' || $type === 'ai')
            ? (app()->db->count('ai_usage_logs', ['account' => $userId]) ?? 0)
            : 0;
        $totalPayment = ($type === 'all' || $type === 'payment')
            ? (app()->db->count('transactions', ['account' => $userId]) ?? 0)
            : 0;

        return response()->json([
            'status' => 'success',
            'page'   => $page,
            'limit'  => $limit,
            'total'  => $totalAi + $totalPayment,
            'logs'   => $result,
        ]);
    }
}