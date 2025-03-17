<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Transaction;
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
        $year = $request->input('year');
        $month = $request->input('month');

        if (!in_array($type, ['monthly', 'yearly'])) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }

        if ($type === 'monthly') {
            $user->transactions()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->each(function ($t) {
                    $this->askAI($t);
                });
        }

        $period = $type === 'monthly' ? Carbon::now()->subMonth()->format('Y-m') : Carbon::now()->subYear()->format('Y');
        
        $transactions_query = $user->transactions()
            ->when($type === 'monthly', fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year))
            ->when($type === 'yearly', fn($q) => $q->whereYear('date', Carbon::createFromYear($year)->subYear()->year));

        $income_transactions_query = clone $transactions_query;
        $expense_transactions_query = clone $transactions_query;

        $totalIncome = $income_transactions_query->where('type', 'income')->sum('amount');
        $totalExpense = $expense_transactions_query->where('type', 'expense')->sum('amount');

        $categoryBreakdown = $transactions_query->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // 存入数据库
        $report = Report::updateOrCreate([
            'user_id' => $user->id,
            'type' => $type,
            'period' => $period,
        ], [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'category_breakdown' => $categoryBreakdown
        ]);

        return response()->json($report);
    }

    private function askAI($transaction)
    {
        $prompt = sprintf(
            "以下是%s账单：%s，仅根据这份账单，生成一份财务报表，
        严格按照 JSON 格式输出，包含字段：amount(金额),type(1 收入 2支出)，category（分类）
        示例 JSON：
            {
                \"amount\": 50,
                \"type\": 1,
                \"category\": \"交通\"
            }
            分类规则：
            - \"收入\"：工资、利息、奖金等正向现金流
            - \"餐饮\"：早餐、外卖、奶茶、大排档
            - \"购物\"：网购、超市购物
            - \"交通\"：停车费、共享单车、地铁、加油费
            - \"娱乐\"：旅游、麻将、奶茶
            - \"其他\"：不属于以上分类的支出
            
            请确保：
            - 如果某笔支出包含 \"返回\" 或 \"退款\"，应从总支出中减去
            - 计算总收入时，必须包括 \"工资\"、\"利息\" 等关键词
            ",
            $transaction['date'],
            $transaction['description']
        );
        // dd($prompt);
        $response = Http::timeout(180)->post('http://127.0.0.1:11434/api/generate', [
            'model' => 'qwen2.5:latest',
            'stream' => false, // 让 Ollama 直接返回完整 JSON
            'format' => 'json', // 确保 Ollama 返回 JSON
            'prompt' => $prompt,
        ]);
        // dd($response->json());
        // 解析 JSON 响应
        $responseData = $response->json()['response'];
        $responseData = trim($responseData);


        // 确保 `content` 字段存在，并解析成 JSON
        $data = json_decode($responseData ?? '{}', true);

        Transaction::where('id', $transaction['id'])->update([
            'category' => $data['category'] ?? '',
            'type' => $data['type'] ?? 0,
            'amount' => $data['amount'] ?? 0,
        ]);
    }

    public function showTrends()
    {
        $reports = Auth::user()->reports()
            ->where('type', 'monthly')
            ->orderBy('period', 'desc')
            ->limit(12)
            ->get();

        return response()->json($reports->map(fn($r) => [
            'month' => $r->period,
            'income' => $r->total_income,
            'expense' => $r->total_expense
        ]));
    }

    public function showDetails($period)
    {
        $report = Auth::user()->reports()
            ->where('type', 'monthly')
            ->where('period', $period)
            ->first();

        return response()->json([
            'category_breakdown' => json_decode($report->category_breakdown ?? '{}'),
        ]);
    }
}
