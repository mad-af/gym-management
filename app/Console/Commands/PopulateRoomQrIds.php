<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PopulateRoomQrIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-room-qr-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate qr_id for rooms based on name and code';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rooms = \App\Models\Room::whereNull('qr_id')->get();
        $count = 0;

        foreach ($rooms as $room) {
            $qrId = $room->name.' - '.$room->code;
            $room->update(['qr_id' => $qrId]);
            $count++;
            $this->info("Updated room: {$room->name} with QR ID: {$qrId}");
        }

        $this->info("Total rooms updated: {$count}");
    }
}
