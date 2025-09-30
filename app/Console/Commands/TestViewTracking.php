<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Walidata;

class TestViewTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:view-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test view tracking functionality for walidata';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        session()->flush();
        
        $walidata = Walidata::first();
        
        if (!$walidata) {
            $this->error('No walidata found');
            return;
        }
        
        $this->info("Testing walidata view tracking:");
        $this->info("Walidata ID: " . $walidata->id);
        $this->info("Before: " . $walidata->view);
        
        // Test increment
        $result = $walidata->incrementViewIfNotSeen();
        $this->info("Increment result: " . ($result ? 'true' : 'false'));
        
        // Refresh from database
        $walidata = $walidata->fresh();
        $this->info("After: " . $walidata->view);
        
        // Test second call (should not increment)
        $result2 = $walidata->incrementViewIfNotSeen();
        $this->info("Second increment result: " . ($result2 ? 'true' : 'false'));
        
        $walidata = $walidata->fresh();
        $this->info("After second call: " . $walidata->view);
    }
}
