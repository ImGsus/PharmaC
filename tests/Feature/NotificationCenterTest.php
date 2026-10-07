<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\ProductExpiryNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NotificationCenterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_access_notifications_center()
    {
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        $this->actingAs($user);

        // Standalone web access redirects to dashboard with popup trigger
        $response = $this->get(route('notifications.index'));
        $response->assertRedirect(route('dashboard', ['open_notifications' => 1]));

        // Modal content endpoint returns 200 with tabs
        $modalRes = $this->get(route('notifications.modal-content'));
        $modalRes->assertStatus(200);
        $modalRes->assertSee('All');
        $modalRes->assertSee('Expired');
        $modalRes->assertSee('Expiring Soon');
    }

    public function test_user_can_view_expired_and_expiring_soon_notifications()
    {
        $user = User::first();
        $this->actingAs($user);

        // Send an expired notification with package box
        $user->notify(new ProductExpiryNotification([
            'type'          => 'expired',
            'product_name'  => 'TestMed Expired',
            'package_label' => 'Package#1',
            'quantity'      => 5,
            'is_active'     => true,
            'expiry_date'   => '2025-01-01',
            'message'       => 'TestMed Expired - Package#1 is expired (5 units) and currently Active.',
            'action_url'    => route('expired'),
        ]));

        // Send an expiring soon notification with package box
        $user->notify(new ProductExpiryNotification([
            'type'          => 'expiring_soon',
            'product_name'  => 'TestMed Soon',
            'package_label' => 'Package#2',
            'quantity'      => 10,
            'days_left'     => 14,
            'is_active'     => true,
            'expiry_date'   => '2026-10-25',
            'message'       => 'TestMed Soon - Package#2 will expire in 14 days.',
            'action_url'    => route('products.index'),
        ]));

        $response = $this->get(route('notifications.modal-content'));
        $response->assertStatus(200);
        $response->assertSee('TestMed Expired');
        $response->assertSee('Package#1');
        $response->assertSee('TestMed Soon');
        $response->assertSee('Package#2');
        $response->assertSee('14 DAYS LEFT');

        // Test filtering tab=expired
        $expiredTab = $this->get(route('notifications.modal-content', ['tab' => 'expired']));
        $expiredTab->assertStatus(200);
        $expiredTab->assertSee('TestMed Expired');

        // Test filtering tab=expiring_soon
        $soonTab = $this->get(route('notifications.modal-content', ['tab' => 'expiring_soon']));
        $soonTab->assertStatus(200);
        $soonTab->assertSee('TestMed Soon');

        // Test search
        $searchRes = $this->get(route('notifications.modal-content', ['search' => 'TestMed Expired']));
        $searchRes->assertStatus(200);
        $searchRes->assertSee('TestMed Expired');
    }

    public function test_mark_single_as_read_ajax()
    {
        $user = User::first();
        $this->actingAs($user);

        $user->notify(new ProductExpiryNotification([
            'type'          => 'expired',
            'product_name'  => 'TestMarkRead',
            'quantity'      => 1,
            'message'       => 'Single read test',
        ]));

        $notification = $user->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $response = $this->postJson(route('notification.mark-single', $notification->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_read_one_redirects()
    {
        $user = User::first();
        $this->actingAs($user);

        $user->notify(new ProductExpiryNotification([
            'type'          => 'expired',
            'product_name'  => 'TestRedirect',
            'quantity'      => 1,
            'action_url'    => route('expired'),
            'message'       => 'Redirect test',
        ]));

        $notification = $user->unreadNotifications()->first();

        $response = $this->get(route('notification.read-one', $notification->id));
        $response->assertRedirect(route('expired'));

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_destroy_ajax_notification()
    {
        $user = User::first();
        $this->actingAs($user);

        $user->notify(new ProductExpiryNotification([
            'type'          => 'expired',
            'product_name'  => 'TestDelete',
            'quantity'      => 1,
            'message'       => 'Delete test',
        ]));

        $notification = $user->notifications()->first();

        $response = $this->deleteJson(route('notification.destroy-ajax', $notification->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
