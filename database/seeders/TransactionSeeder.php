<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Property;
use App\Models\Client;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💰 Seeding Transactions...');

        // Get approved brokers
        $brokers = User::approvedBrokers()->get();
        $properties = Property::all();
        $clients = Client::all();

        if ($brokers->isEmpty() || $properties->isEmpty() || $clients->isEmpty()) {
            $this->command->warn('⚠️  Not enough data to create transactions. Skipping...');
            return;
        }

        $transactions = [];

        // Create transactions for the last 90 days
        for ($i = 0; $i < 25; $i++) {
            $broker = $brokers->random();
            $property = $properties->random();
            $client = $clients->random();
            
            // Random date within last 90 days
            $createdAt = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Transaction types and statuses
            $transactionTypes = ['sale', 'rent', 'lease'];
            $statuses = ['inquiry', 'negotiation', 'finalized', 'cancelled'];
            $transactionType = $transactionTypes[array_rand($transactionTypes)];
            $status = $statuses[array_rand($statuses)];
            
            // Price calculations
            $basePrice = $property->total_price;
            $offeredPrice = $basePrice * (0.85 + (rand(0, 30) / 100)); // 85-115% of base price
            $finalPrice = $status === 'finalized' ? $offeredPrice * (0.95 + (rand(0, 10) / 100)) : null;
            
            // Commission removed from system
            
            $transactions[] = [
                'transaction_number' => 'TXN-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'broker_id' => $broker->id,
                'client_id' => $client->id,
                'property_id' => $property->id,
                'transaction_type' => $transactionType,
                'status' => $status,
                'offered_price' => round($offeredPrice, 2),
                'final_price' => $finalPrice ? round($finalPrice, 2) : null,
                'broker_notes' => $this->generateTransactionNotes($status, $transactionType),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        // Insert transactions
        Transaction::insert($transactions);

        $this->command->info('✅ Transactions seeded successfully!');
        $this->command->info("📊 Summary:");
        $this->command->info("   • Total Transactions: " . count($transactions));
        $this->command->info("   • Finalized: " . collect($transactions)->where('status', 'finalized')->count());
        $this->command->info("   • Pending: " . collect($transactions)->where('status', 'pending')->count());
        $this->command->info("   • Total Sales Value: ₱" . number_format(collect($transactions)->where('status', 'finalized')->sum('final_price'), 2));
    }

    private function generateTransactionNotes($status, $type)
    {
        $notes = [
            'pending' => [
                'Initial inquiry received, waiting for client response',
                'Property viewing scheduled for next week',
                'Client interested, discussing terms',
                'Awaiting client decision on offer',
            ],
            'negotiation' => [
                'Price negotiation in progress',
                'Terms being discussed with client',
                'Counter-offer received, reviewing details',
                'Legal documents being prepared',
            ],
            'finalized' => [
                'Transaction completed successfully',
                'All documents signed and processed',
                'Property transfer completed',
                'Sale completed - great work!',
            ],
            'cancelled' => [
                'Client withdrew from transaction',
                'Property no longer available',
                'Terms could not be agreed upon',
                'Transaction cancelled by mutual agreement',
            ],
        ];

        $typeNotes = $notes[$status] ?? ['Transaction in progress'];
        return $typeNotes[array_rand($typeNotes)];
    }
}
