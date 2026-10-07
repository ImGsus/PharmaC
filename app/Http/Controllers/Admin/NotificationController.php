<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Prepare data for notifications modal/listing.
     */
    protected function getNotificationData(Request $request)
    {
        $user = auth()->user();
        $tab = $request->get('tab', 'all');
        $search = trim((string) $request->get('search', ''));

        // Query base
        $query = $user->notifications();

        // Apply tab filters
        if ($tab === 'unread') {
            $query->whereNull('read_at');
        } elseif ($tab === 'expired') {
            $query->where(function ($q) {
                $q->where('data->type', 'expired')
                  ->orWhere('data->status', 'expired');
            });
        } elseif ($tab === 'expiring_soon') {
            $query->where(function ($q) {
                $q->where('data->type', 'expiring_soon')
                  ->orWhere('data->status', 'expiring_soon');
            });
        } elseif ($tab === 'stock') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%StockAlertNotification%')
                  ->orWhere('data->status', 'low_stock')
                  ->orWhere('data->status', 'out_of_stock');
            });
        }

        // Apply search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('data->product_name', 'like', "%{$search}%")
                  ->orWhere('data->message', 'like', "%{$search}%")
                  ->orWhere('data->package_label', 'like', "%{$search}%");
            });
        }

        $notifications = $query->latest()->paginate(15);

        // Precompute tab badge counts for authenticated user
        $counts = [
            'all'           => $user->notifications()->count(),
            'unread'        => $user->unreadNotifications()->count(),
            'expired'       => $user->notifications()->where(function ($q) {
                                    $q->where('data->type', 'expired')
                                      ->orWhere('data->status', 'expired');
                               })->count(),
            'expiring_soon' => $user->notifications()->where(function ($q) {
                                    $q->where('data->type', 'expiring_soon')
                                      ->orWhere('data->status', 'expiring_soon');
                               })->count(),
            'stock'         => $user->notifications()->where(function ($q) {
                                    $q->where('type', 'like', '%StockAlertNotification%')
                                      ->orWhere('data->status', 'low_stock')
                                      ->orWhere('data->status', 'out_of_stock');
                               })->count(),
        ];

        return compact('notifications', 'counts', 'tab', 'search');
    }

    /**
     * Render modal content via AJAX.
     */
    public function modalContent(Request $request)
    {
        $data = $this->getNotificationData($request);
        return view('admin.notifications.modal-content', $data);
    }

    /**
     * Handle notifications index route.
     * If AJAX, returns modal markup; if direct browser visit, redirects to dashboard with modal trigger.
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->modalContent($request);
        }

        return redirect()->route('dashboard', ['open_notifications' => 1]);
    }

    /**
     * Mark single notification as read and redirect to its action URL.
     */
    public function readOne($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $data = $notification->data ?? [];

        // If a custom action URL was stored, strip its host so it works on any IP/hostname
        $actionUrl = $data['action_url'] ?? null;
        if (!empty($actionUrl)) {
            $path = parse_url($actionUrl, PHP_URL_PATH);
            $query = parse_url($actionUrl, PHP_URL_QUERY);
            return redirect($path . ($query ? '?' . $query : ''));
        }

        $type      = $data['type'] ?? '';
        $productId = $data['product_id'] ?? null;

        if ($type === 'expired') {
            // Go to the expired products page (relative path — works from any host)
            return redirect('/products/expired');
        }

        if ($type === 'expiring_soon') {
            // Go directly to the product edit page if we have a product_id
            if ($productId) {
                return redirect('/products/' . $productId . '/edit');
            }
            return redirect('/products');
        }

        if ($productId) {
            // Stock alerts or other types — go to the product edit page
            return redirect('/products/' . $productId . '/edit');
        }

        return redirect('/dashboard');
    }

    /**
     * Mark single notification as read via AJAX.
     */
    public function markSingleAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success'      => true,
            'unread_count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'unread_count' => 0,
            ]);
        }

        $notification = notify('All notifications marked as read');
        return back()->with($notification);
    }

    public function read(Request $request)
    {
        return $this->markAsRead($request);
    }

    /**
     * Delete a single notification (AJAX or standard request).
     */
    public function destroyAjax(Request $request, $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
        }

        $notification = notify('Notification deleted successfully');
        return back()->with($notification);
    }

    /**
     * Clear all read notifications.
     */
    public function destroyAllRead(Request $request)
    {
        auth()->user()->readNotifications()->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
        }

        $notification = notify('All read notifications have been cleared');
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage (backward compatibility).
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id === 'all') {
            auth()->user()->notifications()->delete();
        } else {
            auth()->user()->notifications()->where('id', $id)->delete();
        }

        $notification = notify('Notification has been deleted');
        return back()->with($notification);
    }
}
