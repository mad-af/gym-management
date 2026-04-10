<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTimezoneCommand extends Command
{
    protected $signature = 'timezone:check';

    protected $description = 'Display timezone alignment diagnostics';

    public function handle(): int
    {
        $appTz = config('app.timezone');

        $this->info('Timezone Diagnostics');
        $this->line('───────────────────');
        $this->line('APP_TIMEZONE:    '.$appTz);
        $this->line('Carbon Now:      '.Carbon::now()->toIsoString().' ('.Carbon::now($appTz)->format('H:i:s T').')');
        $this->line('Carbon Today:    '.Carbon::today()->toDateString());
        $this->line('Server TZ:       '.date_default_timezone_get());

        if (config('database.default') === 'mysql') {
            try {
                $result = DB::select('SELECT @@session.time_zone AS tz');
                $tz = $result[0]->tz ?? 'null';
                $this->line("MySQL Timezone:  {$tz}");
            } catch (\Throwable) {
                $this->warn('MySQL Timezone:  (unavailable)');
            }
        } else {
            $this->line('MySQL Timezone:  (not mysql)');
        }

        return self::SUCCESS;
    }
}
