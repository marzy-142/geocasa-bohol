<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;

class ReconcileTransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reconcile:transactions {--dry-run : Show what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile property and inquiry statuses based on current transaction records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $this->info('Reconciling statuses' . ($dry ? ' (dry-run)' : ''));

        $updatedProps = 0;
        $updatedInquiries = 0;

        // 1) Properties with active transactions but status is available -> set to under_negotiation
        Property::query()
            ->where('status', 'available')
            ->whereHas('transactions', function ($q) {
                $q->whereNotIn('status', ['finalized', 'cancelled']);
            })
            ->with(['transactions' => function ($q) {
                $q->select('id', 'property_id', 'status');
            }])
            ->chunkById(100, function ($props) use (&$updatedProps, $dry) {
                foreach ($props as $p) {
                    $this->line("Property #{$p->id} '{$p->title}' is available but has active tx → under_negotiation");
                    if (!$dry) {
                        $p->updateQuietly(['status' => 'under_negotiation']);
                        $updatedProps++;
                    }
                }
            });

        // 2) Properties marked under_negotiation/reserved but without any active tx -> set to available
        Property::query()
            ->whereIn('status', ['under_negotiation', 'reserved'])
            ->whereDoesntHave('transactions', function ($q) {
                $q->whereNotIn('status', ['finalized', 'cancelled']);
            })
            ->chunkById(100, function ($props) use (&$updatedProps, $dry) {
                foreach ($props as $p) {
                    $this->line("Property #{$p->id} '{$p->title}' marked {$p->status} but no active tx → available");
                    if (!$dry) {
                        $p->updateQuietly(['status' => 'available']);
                        $updatedProps++;
                    }
                }
            });

        // 3) Inquiries linked to transactions: align statuses to transaction status
        Inquiry::query()
            ->whereHas('transaction')
            ->with(['transaction:id,inquiry_id,status'])
            ->chunkById(200, function ($inquiries) use (&$updatedInquiries, $dry) {
                foreach ($inquiries as $inq) {
                    $txn = $inq->transaction;
                    if (!$txn) continue;

                    $map = [
                        'finalized' => 'completed',
                        'cancelled' => 'closed',
                    ];
                    $new = $map[$txn->status] ?? 'in_transaction';
                    if ($inq->status !== $new) {
                        $this->line("Inquiry #{$inq->id} {$inq->status} → {$new} (txn {$txn->status})");
                        if (!$dry) {
                            $inq->updateQuietly(['status' => $new]);
                            $updatedInquiries++;
                        }
                    }
                }
            });

        $this->info("Updated properties: {$updatedProps}");
        $this->info("Updated inquiries: {$updatedInquiries}");

        return self::SUCCESS;
    }
}
