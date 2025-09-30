<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dataset;
use Illuminate\Support\Facades\Session;

class TestViewTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:view-tracker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the ViewTracker trait functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing ViewTracker functionality...');
        $this->newLine();

        // Get or create a test dataset
        $dataset = Dataset::first();
        
        if (!$dataset) {
            $this->error('❌ No datasets found in database. Please create a dataset first.');
            return;
        }

        $this->info("📋 Testing with Dataset: {$dataset->nama}");
        $this->info("🆔 Dataset ID: {$dataset->id}");
        
        // Get initial view count
        $initialViews = $dataset->getViewCount();
        $this->info("👀 Initial view count: {$initialViews}");
        $this->newLine();

        // Test 1: First view should increment
        $this->info('🔬 Test 1: First view increment');
        $wasIncremented = $dataset->incrementViewIfNotSeen();
        $newViews = $dataset->fresh()->getViewCount();
        
        if ($wasIncremented && $newViews === $initialViews + 1) {
            $this->info("✅ PASS: View incremented from {$initialViews} to {$newViews}");
        } else {
            $this->error("❌ FAIL: Expected increment, got wasIncremented={$wasIncremented}, views={$newViews}");
        }
        $this->newLine();

        // Test 2: Second view in same session should NOT increment
        $this->info('🔬 Test 2: Duplicate view prevention');
        $wasIncremented2 = $dataset->incrementViewIfNotSeen();
        $finalViews = $dataset->fresh()->getViewCount();
        
        if (!$wasIncremented2 && $finalViews === $newViews) {
            $this->info("✅ PASS: Duplicate view prevented, views stayed at {$finalViews}");
        } else {
            $this->error("❌ FAIL: Expected no increment, got wasIncremented={$wasIncremented2}, views={$finalViews}");
        }
        $this->newLine();

        // Test 3: Check session tracking
        $this->info('🔬 Test 3: Session tracking');
        $wasViewedInSession = $dataset->wasViewedInSession();
        
        if ($wasViewedInSession) {
            $this->info("✅ PASS: Session correctly tracks that item was viewed");
        } else {
            $this->error("❌ FAIL: Session should show item was viewed");
        }
        $this->newLine();

        // Test 4: Reset session and try again
        $this->info('🔬 Test 4: Session reset functionality');
        $dataset->resetViewSession();
        $wasViewedAfterReset = $dataset->wasViewedInSession();
        
        if (!$wasViewedAfterReset) {
            $this->info("✅ PASS: Session reset successfully");
            
            // Now increment should work again
            $wasIncrementedAfterReset = $dataset->incrementViewIfNotSeen();
            $viewsAfterReset = $dataset->fresh()->getViewCount();
            
            if ($wasIncrementedAfterReset && $viewsAfterReset === $finalViews + 1) {
                $this->info("✅ PASS: View incremented after session reset from {$finalViews} to {$viewsAfterReset}");
            } else {
                $this->error("❌ FAIL: Expected increment after reset, got wasIncremented={$wasIncrementedAfterReset}, views={$viewsAfterReset}");
            }
        } else {
            $this->error("❌ FAIL: Session should be reset");
        }
        $this->newLine();

        // Summary
        $this->info('📊 Test Summary:');
        $this->info("Dataset: {$dataset->nama}");
        $this->info("Initial views: {$initialViews}");
        $this->info("Final views: " . $dataset->fresh()->getViewCount());
        $this->info("Total increments: " . ($dataset->fresh()->getViewCount() - $initialViews));
        
        $this->newLine();
        $this->info('🎉 ViewTracker testing completed!');
        $this->info('💡 The trait prevents duplicate views from the same session.');
        $this->info('💡 Use resetViewSession() to reset session tracking for testing.');

        return 0;
    }
}
