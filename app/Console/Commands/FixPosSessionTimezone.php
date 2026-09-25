<?php

namespace App\Console\Commands;

use App\Models\PosSession;
use Illuminate\Console\Command;

/**
 * One-off backfill for `pos_sessions` rows affected by the timezone bug
 * in versions prior to 2026-08-12.
 *
 * History
 * -------
 * `sessionStart()` originally did:
 *
 *     PosSession::create([
 *         'user_id'    => $userId,
 *         'started_at' => Carbon::now(),
 *     ]);
 *
 * Mass-assign writes the value verbatim to the column, bypassing the
 * Eloquent `datetime` cast. `sessionEnd()` instead did:
 *
 *     $session->ended_at = Carbon::now();
 *     $session->save();
 *
 * which goes through the cast and (under the app's UTC default timezone)
 * writes the same instant with a UTC-converted representation. The
 * `started_at` ended up 8 hours later than `ended_at` for any session
 * that ran for less than 8 hours in wall-clock terms.
 *
 * Because the column has no zone marker, both writes happen "correctly"
 * in their own context; the divergence is only visible when we read both
 * fields back and diff them. So the backfill is a swap, not a shift:
 * if `started_at > ended_at` for a closed session, the two values were
 * written into the wrong columns and we simply swap them.
 *
 * Older sessions may also have been corrupted by the MySQL TIMESTAMP
 * `ON UPDATE CURRENT_TIMESTAMP` behavior. In that case `started_at` was
 * overwritten with the end timestamp on save, producing `started_at ==
 * ended_at` while `created_at` remains the true session start time.
 *
 * Idempotent: re-running the command touches no row whose start already
 * precedes its end, and no row whose start/end pair already matches
 * the created/updated timestamps.
 */
class FixPosSessionTimezone extends Command
{
    protected $signature = 'pos-sessions:fix-timezone {--dry-run : Show what would change without saving}';

    protected $description = 'Swap started_at/ended_at on sessions whose start is later than their end.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $fixed = 0;
        $skipped = 0;

        PosSession::query()
            ->whereNotNull('ended_at')
            ->orderBy('id')
            ->each(function (PosSession $s) use ($dry, &$fixed, &$skipped) {
                $start = $s->started_at;
                $end   = $s->ended_at;
                if (!$start || !$end) {
                    $skipped++;
                    return;
                }

                // First fix the classic timezone swap bug: sessions where
                // the start was written later than the end.
                if ($start->greaterThan($end)) {
                    $this->line(sprintf(
                        '  #%d  start=%s  end=%s  -> swap%s',
                        $s->id,
                        $start->format('Y-m-d H:i:s'),
                        $end->format('Y-m-d H:i:s'),
                        $dry ? '  (dry-run)' : ''
                    ));

                    if (!$dry) {
                        $tmp = $start;
                        $s->started_at = $end;
                        $s->ended_at   = $tmp;
                        $s->save();
                    }

                    $fixed++;
                    return;
                }

                // Then fix rows corrupted by TIMESTAMP ON UPDATE behavior.
                // In that case the row was closed correctly, but started_at
                // was unexpectedly overwritten with the same value as ended_at.
                if ($start->equalTo($end)
                    && $s->created_at
                    && $s->updated_at
                    && $s->created_at->lessThan($s->updated_at)
                    && $s->created_at->equalTo($start) === false
                ) {
                    $this->line(sprintf(
                        '  #%d  start=end=%s  created=%s  updated=%s  -> restore start%s',
                        $s->id,
                        $start->format('Y-m-d H:i:s'),
                        $s->created_at->format('Y-m-d H:i:s'),
                        $s->updated_at->format('Y-m-d H:i:s'),
                        $dry ? '  (dry-run)' : ''
                    ));

                    if (!$dry) {
                        $s->started_at = $s->created_at;
                        $s->save();
                    }

                    $fixed++;
                    return;
                }

                $skipped++;
            });

        $this->info(sprintf(
            '%s %d session(s); skipped %d already-healthy.',
            $dry ? 'Would fix' : 'Fixed',
            $fixed,
            $skipped
        ));

        return self::SUCCESS;
    }
}
