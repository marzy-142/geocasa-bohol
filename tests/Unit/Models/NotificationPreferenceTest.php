<?php

namespace Tests\Unit\Models;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_notification_preference_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'user_id',
            'email_notifications',
            'sms_notifications',
            'push_notifications',
            'inquiry_notifications',
            'transaction_notifications',
            'property_updates',
            'marketing_emails',
            'system_alerts',
            'weekly_digest',
            'monthly_report'
        ];

        $notificationPreference = new NotificationPreference();
        $this->assertEquals($fillable, $notificationPreference->getFillable());
    }

    public function test_notification_preference_has_correct_casts(): void
    {
        $expectedCasts = [
            'id' => 'int',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'inquiry_notifications' => 'boolean',
            'transaction_notifications' => 'boolean',
            'property_updates' => 'boolean',
            'marketing_emails' => 'boolean',
            'system_alerts' => 'boolean',
            'weekly_digest' => 'boolean',
            'monthly_report' => 'boolean'
        ];

        $notificationPreference = new NotificationPreference();
        $casts = $notificationPreference->getCasts();

        foreach ($expectedCasts as $attribute => $expectedCast) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertEquals($expectedCast, $casts[$attribute]);
        }
    }

    public function test_notification_preference_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $notificationPreference = NotificationPreference::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $notificationPreference->user);
        $this->assertEquals($user->id, $notificationPreference->user->id);
    }

    public function test_notification_preference_can_be_created_with_required_attributes(): void
    {
        $user = User::factory()->create();
        
        $notificationPreferenceData = [
            'user_id' => $user->id,
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true
        ];

        $notificationPreference = NotificationPreference::create($notificationPreferenceData);

        $this->assertInstanceOf(NotificationPreference::class, $notificationPreference);
        $this->assertEquals($user->id, $notificationPreference->user_id);
        $this->assertTrue($notificationPreference->email_notifications);
        $this->assertFalse($notificationPreference->sms_notifications);
        $this->assertTrue($notificationPreference->push_notifications);
    }

    public function test_notification_preference_can_be_created_with_all_attributes(): void
    {
        $user = User::factory()->create();
        
        $notificationPreferenceData = [
            'user_id' => $user->id,
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'inquiry_notifications' => true,
            'transaction_notifications' => true,
            'property_updates' => false,
            'marketing_emails' => false,
            'system_alerts' => true,
            'weekly_digest' => true,
            'monthly_report' => false
        ];

        $notificationPreference = NotificationPreference::create($notificationPreferenceData);

        $this->assertInstanceOf(NotificationPreference::class, $notificationPreference);
        $this->assertEquals($user->id, $notificationPreference->user_id);
        $this->assertTrue($notificationPreference->email_notifications);
        $this->assertFalse($notificationPreference->sms_notifications);
        $this->assertTrue($notificationPreference->push_notifications);
        $this->assertTrue($notificationPreference->inquiry_notifications);
        $this->assertTrue($notificationPreference->transaction_notifications);
        $this->assertFalse($notificationPreference->property_updates);
        $this->assertFalse($notificationPreference->marketing_emails);
        $this->assertTrue($notificationPreference->system_alerts);
        $this->assertTrue($notificationPreference->weekly_digest);
        $this->assertFalse($notificationPreference->monthly_report);
    }

    public function test_notification_preference_factory_creates_valid_preference(): void
    {
        $notificationPreference = NotificationPreference::factory()->create();

        $this->assertInstanceOf(NotificationPreference::class, $notificationPreference);
        $this->assertNotNull($notificationPreference->user_id);
        $this->assertIsBool($notificationPreference->email_notifications);
        $this->assertIsBool($notificationPreference->sms_notifications);
        $this->assertIsBool($notificationPreference->push_notifications);
        $this->assertIsBool($notificationPreference->inquiry_notifications);
        $this->assertIsBool($notificationPreference->transaction_notifications);
    }

    public function test_notification_preference_can_enable_all_notifications(): void
    {
        $user = User::factory()->create();
        $notificationPreference = NotificationPreference::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => false,
            'sms_notifications' => false,
            'push_notifications' => false,
            'inquiry_notifications' => false,
            'transaction_notifications' => false,
            'property_updates' => false,
            'marketing_emails' => false,
            'system_alerts' => false,
            'weekly_digest' => false,
            'monthly_report' => false
        ]);
        
        $notificationPreference->enableAllNotifications();
        
        $this->assertTrue($notificationPreference->email_notifications);
        $this->assertTrue($notificationPreference->sms_notifications);
        $this->assertTrue($notificationPreference->push_notifications);
        $this->assertTrue($notificationPreference->inquiry_notifications);
        $this->assertTrue($notificationPreference->transaction_notifications);
        $this->assertTrue($notificationPreference->property_updates);
        $this->assertTrue($notificationPreference->marketing_emails);
        $this->assertTrue($notificationPreference->system_alerts);
        $this->assertTrue($notificationPreference->weekly_digest);
        $this->assertTrue($notificationPreference->monthly_report);
        
        $this->assertDatabaseHas('notification_preferences', [
            'id' => $notificationPreference->id,
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true
        ]);
    }

    public function test_notification_preference_can_disable_all_notifications(): void
    {
        $user = User::factory()->create();
        $notificationPreference = NotificationPreference::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'inquiry_notifications' => true,
            'transaction_notifications' => true,
            'property_updates' => true,
            'marketing_emails' => true,
            'system_alerts' => true,
            'weekly_digest' => true,
            'monthly_report' => true
        ]);
        
        $notificationPreference->disableAllNotifications();
        
        $this->assertFalse($notificationPreference->email_notifications);
        $this->assertFalse($notificationPreference->sms_notifications);
        $this->assertFalse($notificationPreference->push_notifications);
        $this->assertFalse($notificationPreference->inquiry_notifications);
        $this->assertFalse($notificationPreference->transaction_notifications);
        $this->assertFalse($notificationPreference->property_updates);
        $this->assertFalse($notificationPreference->marketing_emails);
        $this->assertFalse($notificationPreference->system_alerts);
        $this->assertFalse($notificationPreference->weekly_digest);
        $this->assertFalse($notificationPreference->monthly_report);
        
        $this->assertDatabaseHas('notification_preferences', [
            'id' => $notificationPreference->id,
            'email_notifications' => false,
            'sms_notifications' => false,
            'push_notifications' => false
        ]);
    }

    public function test_notification_preference_can_enable_essential_notifications_only(): void
    {
        $user = User::factory()->create();
        $notificationPreference = NotificationPreference::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => false,
            'sms_notifications' => false,
            'push_notifications' => false,
            'inquiry_notifications' => false,
            'transaction_notifications' => false,
            'property_updates' => true,
            'marketing_emails' => true,
            'system_alerts' => false,
            'weekly_digest' => true,
            'monthly_report' => true
        ]);
        
        $notificationPreference->enableEssentialOnly();
        
        // Essential notifications should be enabled
        $this->assertTrue($notificationPreference->email_notifications);
        $this->assertTrue($notificationPreference->inquiry_notifications);
        $this->assertTrue($notificationPreference->transaction_notifications);
        $this->assertTrue($notificationPreference->system_alerts);
        
        // Non-essential notifications should be disabled
        $this->assertFalse($notificationPreference->sms_notifications);
        $this->assertFalse($notificationPreference->property_updates);
        $this->assertFalse($notificationPreference->marketing_emails);
        $this->assertFalse($notificationPreference->weekly_digest);
        $this->assertFalse($notificationPreference->monthly_report);
        
        $this->assertDatabaseHas('notification_preferences', [
            'id' => $notificationPreference->id,
            'email_notifications' => true,
            'inquiry_notifications' => true,
            'transaction_notifications' => true,
            'system_alerts' => true,
            'marketing_emails' => false
        ]);
    }

    public function test_notification_preference_can_check_if_email_enabled(): void
    {
        $notificationPreference = NotificationPreference::factory()->create([
            'email_notifications' => true
        ]);
        
        $this->assertTrue($notificationPreference->isEmailEnabled());
        
        $notificationPreference->update(['email_notifications' => false]);
        $this->assertFalse($notificationPreference->isEmailEnabled());
    }

    public function test_notification_preference_can_check_if_sms_enabled(): void
    {
        $notificationPreference = NotificationPreference::factory()->create([
            'sms_notifications' => true
        ]);
        
        $this->assertTrue($notificationPreference->isSmsEnabled());
        
        $notificationPreference->update(['sms_notifications' => false]);
        $this->assertFalse($notificationPreference->isSmsEnabled());
    }

    public function test_notification_preference_can_check_if_push_enabled(): void
    {
        $notificationPreference = NotificationPreference::factory()->create([
            'push_notifications' => true
        ]);
        
        $this->assertTrue($notificationPreference->isPushEnabled());
        
        $notificationPreference->update(['push_notifications' => false]);
        $this->assertFalse($notificationPreference->isPushEnabled());
    }

    public function test_notification_preference_can_check_notification_type_enabled(): void
    {
        $notificationPreference = NotificationPreference::factory()->create([
            'inquiry_notifications' => true,
            'transaction_notifications' => false,
            'property_updates' => true
        ]);
        
        $this->assertTrue($notificationPreference->isNotificationTypeEnabled('inquiry_notifications'));
        $this->assertFalse($notificationPreference->isNotificationTypeEnabled('transaction_notifications'));
        $this->assertTrue($notificationPreference->isNotificationTypeEnabled('property_updates'));
        $this->assertFalse($notificationPreference->isNotificationTypeEnabled('invalid_type'));
    }

    public function test_notification_preference_default_values(): void
    {
        $user = User::factory()->create();
        
        $notificationPreference = NotificationPreference::create([
            'user_id' => $user->id
        ]);

        // Check default values (assuming they default to true for essential notifications)
        $this->assertTrue($notificationPreference->email_notifications);
        $this->assertFalse($notificationPreference->sms_notifications);
        $this->assertTrue($notificationPreference->push_notifications);
        $this->assertTrue($notificationPreference->inquiry_notifications);
        $this->assertTrue($notificationPreference->transaction_notifications);
        $this->assertTrue($notificationPreference->property_updates);
        $this->assertFalse($notificationPreference->marketing_emails);
        $this->assertTrue($notificationPreference->system_alerts);
        $this->assertFalse($notificationPreference->weekly_digest);
        $this->assertFalse($notificationPreference->monthly_report);
    }

    public function test_notification_preference_user_relationship_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        NotificationPreference::create([
            'email_notifications' => true,
            'sms_notifications' => false
        ]);
    }

    public function test_notification_preference_can_update_specific_preferences(): void
    {
        $user = User::factory()->create();
        $notificationPreference = NotificationPreference::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => false,
            'inquiry_notifications' => false,
            'marketing_emails' => false
        ]);
        
        $notificationPreference->updatePreferences([
            'email_notifications' => true,
            'inquiry_notifications' => true,
            'marketing_emails' => true
        ]);
        
        $this->assertTrue($notificationPreference->email_notifications);
        $this->assertTrue($notificationPreference->inquiry_notifications);
        $this->assertTrue($notificationPreference->marketing_emails);
        
        $this->assertDatabaseHas('notification_preferences', [
            'id' => $notificationPreference->id,
            'email_notifications' => true,
            'inquiry_notifications' => true,
            'marketing_emails' => true
        ]);
    }

    public function test_notification_preference_can_get_enabled_channels(): void
    {
        $notificationPreference = NotificationPreference::factory()->create([
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true
        ]);
        
        $enabledChannels = $notificationPreference->getEnabledChannels();
        
        $this->assertContains('email', $enabledChannels);
        $this->assertContains('push', $enabledChannels);
        $this->assertNotContains('sms', $enabledChannels);
        $this->assertCount(2, $enabledChannels);
    }

    public function test_notification_preference_can_get_enabled_types(): void
    {
        $notificationPreference = NotificationPreference::factory()->create([
            'inquiry_notifications' => true,
            'transaction_notifications' => false,
            'property_updates' => true,
            'marketing_emails' => false,
            'system_alerts' => true
        ]);
        
        $enabledTypes = $notificationPreference->getEnabledTypes();
        
        $this->assertContains('inquiry_notifications', $enabledTypes);
        $this->assertContains('property_updates', $enabledTypes);
        $this->assertContains('system_alerts', $enabledTypes);
        $this->assertNotContains('transaction_notifications', $enabledTypes);
        $this->assertNotContains('marketing_emails', $enabledTypes);
        $this->assertCount(3, $enabledTypes);
    }

    public function test_notification_preference_soft_deletes(): void
    {
        $notificationPreference = NotificationPreference::factory()->create();
        $preferenceId = $notificationPreference->id;
        
        $notificationPreference->delete();
        
        $this->assertSoftDeleted('notification_preferences', ['id' => $preferenceId]);
        $this->assertNull(NotificationPreference::find($preferenceId));
        $this->assertNotNull(NotificationPreference::withTrashed()->find($preferenceId));
    }

    public function test_notification_preference_can_be_restored(): void
    {
        $notificationPreference = NotificationPreference::factory()->create();
        $preferenceId = $notificationPreference->id;
        
        $notificationPreference->delete();
        $this->assertSoftDeleted('notification_preferences', ['id' => $preferenceId]);
        
        $notificationPreference->restore();
        $this->assertDatabaseHas('notification_preferences', [
            'id' => $preferenceId,
            'deleted_at' => null
        ]);
    }
}