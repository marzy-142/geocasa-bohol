<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\{User, Property, Inquiry, Transaction};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class QueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function broker_dashboard_queries_are_optimized()
    {
        $broker = User::factory()->broker()->create();
        
        // Create test data
        Property::factory()->count(50)->create(['broker_id' => $broker->id]);
        Inquiry::factory()->count(100)->create();
        Transaction::factory()->count(30)->create(['broker_id' => $broker->id]);
        
        DB::enableQueryLog();
        
        $this->actingAs($broker)->get('/broker/dashboard');
        
        $queries = DB::getQueryLog();
        
        // Assert reasonable number of queries (adjust based on your optimization)
        $this->assertLessThan(10, count($queries), 'Dashboard should use fewer than 10 queries');
    }
}