<?php

namespace App\Console\Commands;

use App\Http\Controllers\ReportController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate {year?} {month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成月度和年度财务报告';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? Carbon::now()->year;
        $month = $this->argument('month') ?? Carbon::now()->month;

        $this->info("开始生成 AI 财务报表...");

        $this->info("正在生成月报...");

        User::all()->each(function ($user) use ($year, $month){
            Auth::login($user);

            $this->info("生成 {$user->name} 的月报...");
            app(ReportController::class)->generateReport(
                new \Illuminate\Http\Request(['type' => 'monthly', 'year' => $year, 'month' => $month])
            );

            // 是否元旦当天
            // if (Carbon::now()->isSameDay(Carbon::now()->firstOfYear())) {
            if ($month == 1) {
                $this->info("生成 {$user->name} 的年报...");
                app(ReportController::class)->generateReport(
                    new \Illuminate\Http\Request(['type' => 'yearly', 'year' => $year])
                );
            }
            
            Auth::logout();
        });

        $this->info("报表生成完成！");
    }
}
