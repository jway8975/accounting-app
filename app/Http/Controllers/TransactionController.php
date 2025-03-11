<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // 获取当前用户的所有交易
    public function index()
    {
        return response()->json(Auth::user()->transactions()->orderByDesc('date')->paginate(20));
    }

    // 存储新的交易记录
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'type' => 'required|in:1,2', // 1=收入, 2=支出
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $transaction = Auth::user()->transactions()->create($validated);

        return response()->json($transaction, 201);
    }

    // 获取单个交易详情
    public function show($id)
    {
        $transaction = Auth::user()->transactions()->findOrFail($id);
        return response()->json($transaction);
    }

    // 更新交易记录
    public function update(Request $request, $id)
    {
        $transaction = Auth::user()->transactions()->findOrFail($id);
        $transaction->update($request->all());
        return response()->json($transaction);
    }

    // 删除交易
    public function destroy($id)
    {
        Auth::user()->transactions()->findOrFail($id)->delete();
        return response()->json(['message' => '删除成功']);
    }
}
