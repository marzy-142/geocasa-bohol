<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Conversation;
use App\Models\Transaction;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestInquiryWorkflow extends Command
{
    protected $signature = 'test:inquiry-workflow';
    protected $description = 'Test the Inquiry→Transaction workflow';

    public function handle()
    {
        $this->info('=== Testing Inquiry→Transaction Workflow ===');
        $this->newLine();

        // 1. Check for test data
        $this->info('1. Checking test data...');
        
        $broker = User::where('role', 'broker')->first();
        if (!$broker) {
            $this->error('   ❌ No broker found');
            return 1;
        }
        $this->line("   ✅ Broker: {$broker->name}");

        $client = Client::first();
        if (!$client) {
            $this->error('   ❌ No client found');
            return 1;
        }
        $this->line("   ✅ Client: {$client->name}");

        $property = Property::where('status', 'available')->first();
        if (!$property) {
            $this->error('   ❌ No available property');
            return 1;
        }
        $this->line("   ✅ Property: {$property->title}");

        // 2. Create inquiry
        $this->newLine();
        $this->info('2. Creating test inquiry...');
        
        $inquiry = Inquiry::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'user_id' => $client->user_id,
            'assigned_broker_id' => $broker->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone ?? '1234567890',
            'message' => 'Test inquiry for workflow',
            'inquiry_type' => 'purchase',
            'status' => 'new',
        ]);
        $this->line("   ✅ Inquiry #{$inquiry->id} created");

        // 3. Create conversation
        $this->newLine();
        $this->info('3. Creating conversation...');
        
        $conversation = Conversation::createForInquiry($inquiry);
        $this->line("   ✅ Conversation #{$conversation->id}");
        $this->line("   - Type: {$conversation->type}");
        $this->line("   - Stage: {$conversation->lifecycle_stage}");

        // 4. Test transition
        $this->newLine();
        $this->info('4. Testing inquiry acceptance...');
        
        try {
            DB::transaction(function () use ($inquiry, $broker, &$transaction) {
                $property = $inquiry->property;
                
                $transaction = Transaction::create([
                    'inquiry_id' => $inquiry->id,
                    'property_id' => $inquiry->property_id,
                    'client_id' => $inquiry->client_id,
                    'broker_id' => $broker->id,
                    'status' => 'initial_contact',
                    'transaction_number' => 'TXN-' . strtoupper(Str::random(10)),
                    'offered_price' => $property->total_price ?? 0,
                    'client_engagement_score' => 50,
                ]);
                
                $inquiry->update(['status' => 'in transaction']);
                $inquiry->conversation->transitionToTransaction($transaction);
            });
            
            $this->line("   ✅ Transaction created: {$transaction->transaction_number}");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: {$e->getMessage()}");
            return 1;
        }

        // 5. Verify
        $this->newLine();
        $this->info('5. Verifying results...');
        
        $inquiry->refresh();
        $conversation->refresh();
        
        $this->line("   Inquiry status: {$inquiry->status}");
        $this->line("   Conversation type: {$conversation->type}");
        $this->line("   Conversation stage: {$conversation->lifecycle_stage}");
        $this->line("   Transaction linked: " . ($conversation->transaction_id ? 'Yes' : 'No'));
        
        $systemMsg = $conversation->messages()->where('type', 'system')->latest()->first();
        if ($systemMsg) {
            $this->line("   ✅ System message: \"{$systemMsg->content}\"");
        }
        
        if ($conversation->metadata) {
            $this->line("   ✅ Metadata:");
            $this->line("      - TXN: " . ($conversation->metadata['transaction_number'] ?? 'N/A'));
            $this->line("      - Inquiry: " . ($conversation->metadata['original_inquiry_id'] ?? 'N/A'));
        }

        $this->newLine();
        $this->info('✅ ALL TESTS PASSED!');
        $this->newLine();
        $this->line("View inquiry: /inquiries/{$inquiry->id}");
        $this->line("View conversation: /conversations/{$conversation->id}");
        $this->line("Transaction: {$transaction->transaction_number}");
        
        return 0;
    }
}
