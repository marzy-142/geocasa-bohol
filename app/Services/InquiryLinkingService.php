<?php

namespace App\Services;

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InquiryLinkingService
{
    /**
     * Link existing inquiries and clients to a newly registered user by email
     *
     * @param User $user
     * @return array
     */
    public function linkExistingInquiriesToUser(User $user): array
    {
        $linkedInquiries = 0;
        $linkedClients = 0;

        try {
            DB::beginTransaction();

            // Find and link inquiries with matching email that don't have a user_id
            $inquiries = Inquiry::where('email', $user->email)
                ->whereNull('user_id')
                ->get();

            foreach ($inquiries as $inquiry) {
                $inquiry->update(['user_id' => $user->id]);
                $linkedInquiries++;
            }

            // Find and link clients with matching email that don't have a user_id
            $clients = Client::where('email', $user->email)
                ->whereNull('user_id')
                ->get();

            foreach ($clients as $client) {
                $client->update(['user_id' => $user->id]);
                $linkedClients++;

                // Also link any inquiries associated with this client
                $clientInquiries = Inquiry::where('client_id', $client->id)
                    ->whereNull('user_id')
                    ->get();

                foreach ($clientInquiries as $clientInquiry) {
                    $clientInquiry->update(['user_id' => $user->id]);
                    $linkedInquiries++;
                }
            }

            DB::commit();

            Log::info('Successfully linked existing data to user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'linked_inquiries' => $linkedInquiries,
                'linked_clients' => $linkedClients
            ]);

            return [
                'success' => true,
                'linked_inquiries' => $linkedInquiries,
                'linked_clients' => $linkedClients,
                'message' => "Successfully linked {$linkedInquiries} inquiries and {$linkedClients} clients to your account."
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to link existing data to user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'linked_inquiries' => 0,
                'linked_clients' => 0,
                'message' => 'Failed to link existing inquiries to your account.'
            ];
        }
    }

    /**
     * Check if there are existing inquiries or clients for an email
     *
     * @param string $email
     * @return array
     */
    public function checkExistingDataForEmail(string $email): array
    {
        $inquiriesCount = Inquiry::where('email', $email)
            ->whereNull('user_id')
            ->count();

        $clientsCount = Client::where('email', $email)
            ->whereNull('user_id')
            ->count();

        return [
            'has_existing_data' => ($inquiriesCount > 0 || $clientsCount > 0),
            'inquiries_count' => $inquiriesCount,
            'clients_count' => $clientsCount
        ];
    }
}