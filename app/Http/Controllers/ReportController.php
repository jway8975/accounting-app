<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type'); // 'monthly' or 'yearly'
        
        if (!in_array($type, ['monthly', 'yearly'])) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }

        $period = $type === 'monthly' ? Carbon::now()->subMonth()->format('Y-m') : Carbon::now()->subYear()->format('Y');

        $transactions = $user->transactions()
            ->when($type === 'monthly', fn($q) => $q->whereMonth('date', Carbon::now()->month))
            ->when($type === 'yearly', fn($q) => $q->whereYear('date', Carbon::now()->subYear()->year))
            ->get(['date', 'description', 'amount', 'type']);

        $summary = $this->askAI($transactions);
        
        // 存入数据库
        $report = Report::updateOrCreate([
            'user_id' => $user->id,
            'type' => $type,
            'period' => $period,
        ], [
            'total_income' => $summary['total_income'] ?? 0,
            'total_expense' => $summary['total_expense'] ?? 0,
            'category_breakdown' => json_encode($summary['category_breakdown'] ?? []),
            'content' => "财务分析：\n总收入：" . ($summary['total_income'] ?? 0) .
                         "\n总支出：" . ($summary['total_expense'] ?? 0),
        ]);

        return response()->json($report);
    }

    private function askAI($transactions)
    {
        $inputText = $transactions->map(fn($t) => $t->date . ': ' . $t->description)->implode("\n");

        $response = Http::timeout(120)->post('http://127.0.0.1:11434/api/generate', [
            'model' => 'huihui_ai/deepseek-r1-abliterated:7b',
            'stream' => false, // 让 Ollama 直接返回完整 JSON
            'format' => 'json', // 确保 Ollama 返回 JSON
            'prompt' => "请根据以下交易数据，生成 JSON 格式的财务报表：
            - total_income（总收入）
            - total_expense（总支出）
            - category_breakdown（按类别统计支出）

            示例 JSON：
            {
                \"total_income\": 5000,
                \"total_expense\": 3200,
                \"category_breakdown\": {
                    \"餐饮\": 1200,
                    \"购物\": 800,
                    \"交通\": 600
                }
            }

            交易记录如下：
            $inputText"
        ]);

        // 解析 JSON 响应
        $responseData = $response->json()['response'];
        $responseData = trim($responseData);
        

        // 确保 `content` 字段存在，并解析成 JSON
        return json_decode($responseData ?? '{}', true);
    }
}
