<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One cashier shift at the POS / Cashier page.
 *
 *  - started_at: timestamp the cashier clicked "Start Session"
 *  - ended_at:   timestamp they clicked "End Session" (NULL while open)
 *
 * Total earnings is NOT stored on this row — it is computed on demand from
 * Sale::total_price rows whose created_at falls inside [started_at, ended_at]
 * for the session's cashier. This keeps the source of truth in sales.
 */
class PosSession extends Model
{
    use HasFactory;

    protected $table = 'pos_sessions';

    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cashier_id', 'user_id');
    }

    public function sessionSales()
    {
        return $this->hasMany(Sale::class, 'pos_session_id');
    }

    /**
     * Bounds used when querying sales for this session. If the session is
     * still open, the upper bound is "now" so the running total stays
     * accurate while the cashier is still working.
     *
     * Historical rows from before the timezone fix (see
     * `php artisan pos-sessions:fix-timezone`) may still have started_at
     * later than ended_at in the DB. When that happens we swap the
     * bounds here so the earnings/sales counts and the duration come
     * out as the cashier would expect. The `FixPosSessionTimezone`
     * command is the proper repair for those rows; this is a read-side
     * safety net so the UI doesn't keep showing 0 orders / 0 earnings
     * for already-broken sessions.
     */
    public function sessionBounds(): array
    {
        $start = $this->started_at;
        $end   = $this->ended_at ?: Carbon::now();

        if ($start && $end && $start->greaterThan($end)) {
            return [$end, $start];
        }

        return [$start, $end];
    }

    /**
     * Live sum of all sales recorded during this session by this cashier.
     */
    public function totalEarnings(): float
    {
        $rows = $this->sessionSales()
            ->select('customer_name', 'payment_method', 'payment_amount', 'discount', 'created_at', 'total_price')
            ->get();

        $transactions = [];
        foreach ($rows as $row) {
            $key = (
                ($row->customer_name ?? '') . '|' .
                ($row->payment_method ?? '') . '|' .
                ($row->payment_amount ?? '') . '|' .
                ($row->discount ?? 0) . '|' .
                ($row->created_at ? $row->created_at->format('Y-m-d H:i') : '')
            );
            if (!isset($transactions[$key])) {
                $transactions[$key] = [
                    'subtotal' => 0.0,
                    'discount' => (float) ($row->discount ?? 0),
                ];
            }
            $transactions[$key]['subtotal'] += (float) $row->total_price;
        }

        return round((float) collect($transactions)->sum(function ($transaction) {
            return max(0, $transaction['subtotal'] - $transaction['discount']);
        }), 2);
    }

    /**
     * Number of distinct "Save clicks" in the session — i.e. the count of
     * completed transactions. Uses the same minute+customer+payment grouping
     * the receipt uses, but we approximate by counting sales attached to
     * this session.
     */
    public function saleCount(): int
    {
        $rows = $this->sessionSales()
            ->select('customer_name', 'payment_method', 'payment_amount', 'discount', 'created_at')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $key = (
                ($row->customer_name ?? '') . '|' .
                ($row->payment_method ?? '') . '|' .
                ($row->payment_amount ?? '') . '|' .
                ($row->discount ?? 0) . '|' .
                ($row->created_at ? $row->created_at->format('Y-m-d H:i') : '')
            );
            $groups[$key] = true;
        }

        return count($groups);
    }

    public function durationMinutes(): int
    {
        [$start, $end] = $this->sessionBounds();

        if (!$start || !$end) {
            return 0;
        }

        $diff = abs($start->diffInSeconds($end));
        if ($diff < 60) {
            return 0;
        }

        return (int) floor($diff / 60);
    }

    /**
     * Find the cashier's currently-open session, if any.
     */
    public static function openForUser(int $userId): ?self
    {
        return static::query()
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}