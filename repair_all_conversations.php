<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv, true);

echo "=== Global Conversation Participant Repair ===\n";
echo 'Mode: '.($dryRun ? 'DRY-RUN' : 'APPLY')."\n\n";

$total = 0;
$skippedNoConversation = 0;
$skippedNoClientUser = 0;
$fixedJsonOnly = 0;
$fixedPivotOnly = 0;
$fixedBoth = 0;
$linkedUsers = 0;
$unresolved = [];
$alreadyOk = 0;

$inquiries = Inquiry::with(['conversation.participantUsers', 'client', 'user'])
    ->whereHas('conversation')
    ->get();

echo 'Inquiries with conversations found: '.$inquiries->count()."\n\n";

foreach ($inquiries as $inquiry) {
    $total++;
    $conv = $inquiry->conversation;
    if (!$conv) { $skippedNoConversation++; continue; }

    $participants = $conv->participants ?? [];
    if (!is_array($participants)) { $participants = []; }

    $pivotParticipants = $conv->participantUsers()->pluck('user_id')->toArray();

    $clientUserId = $inquiry->user_id;

    // Try linking via client->user_id
    if (!$clientUserId && $inquiry->client && $inquiry->client->user_id) {
        $clientUserId = $inquiry->client->user_id;
    }

    // Try linking via email
    if (!$clientUserId && $inquiry->email) {
        $user = User::where('email', $inquiry->email)->first();
        if ($user) {
            $clientUserId = $user->id;
            if (!$dryRun) {
                // Backfill links
                if ($inquiry->user_id !== $user->id) {
                    $inquiry->user_id = $user->id;
                    $inquiry->save();
                    $linkedUsers++;
                }
                if ($inquiry->client && !$inquiry->client->user_id) {
                    $inquiry->client->user_id = $user->id;
                    $inquiry->client->save();
                }
            }
        }
    }

    if (!$clientUserId) {
        $skippedNoClientUser++;
        $unresolved[] = [
            'inquiry_id' => $inquiry->id,
            'conversation_id' => $conv->id,
            'client_id' => $inquiry->client_id,
            'inquiry_email' => $inquiry->email,
            'client_email' => $inquiry->client->email ?? null,
            'note' => 'No matching user by user_id/client.user_id/email'
        ];
        continue;
    }

    $needsJson = !in_array($clientUserId, $participants, true);
    $needsPivot = !in_array($clientUserId, $pivotParticipants, true);

    if (!$needsJson && !$needsPivot) { $alreadyOk++; continue; }

    echo "Repairing conversation #{$conv->id} for inquiry #{$inquiry->id} | client user #{$clientUserId}\n";

    if ($needsJson) {
        echo " - Add to JSON participants\n";
        if (!$dryRun) {
            $participants[] = $clientUserId;
            $conv->participants = array_values(array_unique($participants));
            $conv->save();
        }
    }

    if ($needsPivot) {
        echo " - Add to pivot participants\n";
        if (!$dryRun) {
            $conv->participantUsers()->syncWithoutDetaching([$clientUserId]);
        }
    }

    if ($needsJson && $needsPivot) { $fixedBoth++; }
    elseif ($needsJson) { $fixedJsonOnly++; }
    elseif ($needsPivot) { $fixedPivotOnly++; }
}

echo "\n=== Summary ===\n";
echo "Processed inquiries: {$total}\n";
echo "Linked users by email: {$linkedUsers}\n";
echo "Fixed both JSON+pivot: {$fixedBoth}\n";
echo "Fixed JSON only: {$fixedJsonOnly}\n";
echo "Fixed pivot only: {$fixedPivotOnly}\n";
echo "Skipped (no conversation): {$skippedNoConversation}\n";
echo "Skipped (no client user resolvable): {$skippedNoClientUser}\n";
echo "Already OK (no change needed): {$alreadyOk}\n";

// Quick visibility check for a sample of affected conversations
$sample = Conversation::whereHas('inquiry')
    ->where(function($q){ $q->whereNull('participants')->orWhereRaw('JSON_LENGTH(participants) = 0'); })
    ->count();
echo "Conversations still with empty JSON participants (post-repair): {$sample}\n";

if (!empty($unresolved)) {
    echo "\n=== Unresolved (need user account to link) ===\n";
    foreach ($unresolved as $row) {
        echo "Inquiry #{$row['inquiry_id']} | Conv #{$row['conversation_id']} | Client #{$row['client_id']} | Email: {$row['inquiry_email']}";
        if ($row['client_email'] && $row['client_email'] !== $row['inquiry_email']) {
            echo " (client email: {$row['client_email']})";
        }
        echo "\n";
    }
}
