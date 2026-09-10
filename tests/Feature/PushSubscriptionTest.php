<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use Illuminate\Contracts\Events\Dispatcher;
use Livewire\Livewire;
use Minishlink\WebPush\ContentEncoding;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\SubscriptionInterface;
use Minishlink\WebPush\WebPush;
use NotificationChannels\WebPush\ReportHandler;
use NotificationChannels\WebPush\WebPushChannel;

final class CapturingWebPush extends WebPush
{
    /**
     * @var array<int, array{0: Subscription, 1: ?string, 2: array, 3: array}>
     */
    public array $calls = [];

    public function __construct() {}

    public function queueNotification(SubscriptionInterface $subscription, ?string $payload = null, array $options = [], array $auth = []): void
    {
        $this->calls[] = [$subscription, $payload, $options];
    }

    public function flush(?int $batchSize = null): Generator
    {
        yield from [];
    }
}

it('stores a push subscription for the authenticated user', function () {
    $user = User::factory()->admin()->create();
    $endpoint = 'https://fcm.googleapis.com/fcm/send/demo-subscription';

    $this->actingAs($user)
        ->postJson(route('push-subscription.store'), [
            'endpoint' => $endpoint,
            'public_key' => 'test-public-key',
            'auth_token' => 'test-auth-token',
            'content_encoding' => 'aes128gcm',
        ])
        ->assertCreated();

    $subscription = $user->pushSubscriptions()->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->endpoint)->toBe($endpoint);
    expect($subscription->public_key)->toBe('test-public-key');
    expect($subscription->auth_token)->toBe('test-auth-token');
    expect($subscription->content_encoding)->toBe(ContentEncoding::aes128gcm);
});

it('redirects guests to sign in when storing a push subscription', function () {
    $this->post(route('push-subscription.store'), [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo-subscription',
    ])->assertRedirect(route('login'));
});

it('rejects a push subscription without a valid endpoint', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->postJson(route('push-subscription.store'), [
            'endpoint' => 'not-a-url',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('endpoint');

    expect($user->pushSubscriptions()->count())->toBe(0);
});

it('deletes the matching push subscription of the user', function () {
    $user = User::factory()->admin()->create();
    $otherUser = User::factory()->supervisor()->create();
    $endpoint = 'https://fcm.googleapis.com/fcm/send/demo-subscription';

    $user->updatePushSubscription($endpoint, 'key-a', 'token-a');
    $user->updatePushSubscription('https://fcm.googleapis.com/fcm/send/another-subscription', 'key-b', 'token-b');
    $otherUser->updatePushSubscription($endpoint, 'key-c', 'token-c');

    $this->actingAs($user)
        ->deleteJson(route('push-subscription.destroy'), ['endpoint' => $endpoint])
        ->assertNoContent();

    expect($user->pushSubscriptions()->pluck('endpoint')->all())
        ->toBe(['https://fcm.googleapis.com/fcm/send/another-subscription']);
    expect($otherUser->pushSubscriptions()->pluck('endpoint')->all())->toBe([$endpoint]);
});

it('redirects guests to sign in when deleting a push subscription', function () {
    $this->delete(route('push-subscription.destroy'), [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo-subscription',
    ])->assertRedirect(route('login'));
});

it('sends a web push to the staff subscription when a ticket is opened', function () {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $admin->updatePushSubscription('https://fcm.googleapis.com/fcm/send/demo-subscription', 'key', 'token');

    $webPush = new CapturingWebPush;
    $channel = new WebPushChannel($webPush, new ReportHandler(app(Dispatcher::class)));

    $channel->send($admin, new NewTicketNotification($ticket));

    expect($webPush->calls)->toHaveCount(1);

    [$subscription, $payload, $options] = $webPush->calls[0];
    expect($subscription->getEndpoint())->toBe('https://fcm.googleapis.com/fcm/send/demo-subscription');
    expect($subscription->getPublicKey())->toBe('key');
    expect($options['TTL'])->toBe(86400);

    $data = json_decode($payload, true);
    expect($data['title'])->toBe('Novo ticket '.$ticket->tracking_code);
    expect($data['body'])->not->toBeEmpty();
    expect($data['data']['url'])->toBe(route('admin.tickets.show', $ticket));
});

it('prompts the user to enable browser notifications from the bell', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->assertSeeHtml('data-test="enable-push"')
        ->assertDontSeeHtml('data-test="disable-push"');
});

it('shows the disable option when the user has a push subscription', function () {
    $admin = User::factory()->admin()->create();
    $admin->updatePushSubscription('https://fcm.googleapis.com/fcm/send/demo-subscription', 'key', 'token');

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->assertSeeHtml('data-test="disable-push"')
        ->assertDontSeeHtml('data-test="enable-push"');
});

it('deletes the subscriptions when the user disables browser notifications', function () {
    $admin = User::factory()->admin()->create();
    $admin->updatePushSubscription('https://fcm.googleapis.com/fcm/send/demo-subscription', 'key', 'token');

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->call('disableBrowserNotifications')
        ->assertSeeHtml('data-test="enable-push"')
        ->assertDontSeeHtml('data-test="disable-push"');

    expect($admin->pushSubscriptions()->count())->toBe(0);
});

it('turns the bell into the enabled state when a push subscription is saved', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->call('onPushSubscriptionSaved')
        ->assertSeeHtml('data-test="disable-push"')
        ->assertDontSeeHtml('data-test="enable-push"');
});
