<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Conversation;

class SyncConversationParticipants extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'conversation:sync-participants {--dry-run : Show actions without modifying data}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill missing user participants for inquiry and transaction conversations (e.g., clients who registered after creating an inquiry).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $updated = 0;
        $skipped = 0;

        $this->info('Scanning conversations for missing dynamic participants...');

        Conversation::with(['inquiry.client', 'transaction'])
            ->chunk(200, function ($conversations) use (&$updated, &$skipped, $dryRun) {
                foreach ($conversations as $conversation) {
                    $beforeJson = $conversation->participants ?? [];
                    $beforePivot = $conversation->participantUsers()->pluck('users.id')->toArray();

                    // Capture prospective additions without committing if dry-run
                    $prospective = [];

                    // Inquiry client user
                    if ($conversation->inquiry && $conversation->inquiry->client && $conversation->inquiry->client->user_id) {
                        $cid = $conversation->inquiry->client->user_id;
                        if (!$conversation->hasParticipant($cid)) {
                            $prospective[] = $cid;
                            if (!$dryRun) {
                                $conversation->addParticipant($cid);
                            }
                        }
                    }

                    // Transaction participants
                    if ($conversation->transaction) {
                        foreach (array_filter([$conversation->transaction->broker_id, $conversation->transaction->client_id]) as $tid) {
                            if (!$conversation->hasParticipant($tid)) {
                                $prospective[] = $tid;
                                if (!$dryRun) {
                                    $conversation->addParticipant($tid);
                                }
                            }
                        }
                    }

                    if (empty($prospective)) {
                        $skipped++;
                        continue;
                    }

                    $afterJson = $dryRun ? $beforeJson : $conversation->participants;
                    $afterPivot = $dryRun ? $beforePivot : $conversation->participantUsers()->pluck('users.id')->toArray();

                    $updated++;
                    $this->line(sprintf(
                        "[Conversation %d] Added: %s | Dry-run: %s",
                        $conversation->id,
                        implode(',', $prospective),
                        $dryRun ? 'YES' : 'NO'
                    ));
                }
            });

        $this->info("Completed. Updated: {$updated}, Skipped: {$skipped}.");
        if ($dryRun) {
            $this->warn('Run again without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
