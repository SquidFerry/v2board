<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetTraffic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:traffic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '流量清空';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::where('expired_at', '!=', NULL);
        $resetTrafficMethod = config('v2board.reset_traffic_method', 0);
        switch ((int)$resetTrafficMethod) {
            // 1 a month
            case 0:
                $this->resetByMonthFirstDay($user);
                break;
            // expire day
            case 1:
                $this->resetByExpireDay($user);
                break;
        }
    }

    private function resetByMonthFirstDay($user):void
    {
        if ((string)date('d') === '01') {
            $user->update([
                'u' => 0,
                'd' => 0
            ]);
        }
    }

    private function resetByExpireDay($user):void
    {
        $date = date('Y-m-d', time());
        $startAt = strtotime((string)$date);
        $endAt = (int)$startAt + 24 * 3600;
        $lastDay = date('d', strtotime('last day of +0 months'));
        if ((string)$lastDay === '29') {
            $endAt = (int)$startAt + 72 * 3600;
        }
        if ((string)$lastDay === '30') {
            $endAt = (int)$startAt + 48 * 3600;
        }
        $user->where('expired_at', '>=', (int)$startAt)
            ->where('expired_at', '<', (int)$endAt)
            ->update([
                'u' => 0,
                'd' => 0
            ]);
    }
}
