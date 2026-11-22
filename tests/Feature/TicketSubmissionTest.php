<?php

namespace Tests\Feature;

use App\Jobs\ClassifyTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_ticket_is_saved_and_queued(): void
    {
        Queue::fake();

        $response = $this->post('/tickets', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'subject' => 'Login issue',
            'message' => 'I cannot log in to my account.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'email' => 'test@example.com',
            'status' => 'new',
            'ai_status' => 'pending',
        ]);

        Queue::assertPushed(ClassifyTicket::class, 1);
    }

    public function test_short_message_is_rejected(): void
    {
        Queue::fake();

        $response = $this->post('/tickets', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'subject' => 'Login issue',
            'message' => 'Too short',
        ]);

        $response->assertSessionHasErrors('message');

        $this->assertDatabaseCount('tickets', 0);

        Queue::assertNotPushed(ClassifyTicket::class);
    }
}
