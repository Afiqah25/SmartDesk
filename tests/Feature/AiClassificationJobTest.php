<?php

namespace Tests\Feature;

use App\Contracts\TicketAiService;
use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiClassificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_classification_is_saved_to_database(): void
    {
        $ticket = Ticket::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'subject' => 'Unable to login',
            'message' => 'I cannot access my account. Please help.',
        ]);

        $ai = Mockery::mock(TicketAiService::class);

        $ai->shouldReceive('classify')
            ->once()
            ->andReturn([
                'category' => 'account',
                'priority' => 'high',
                'sentiment' => 'negative',
                'summary' => 'Customer cannot log in.',
                'language' => 'en',
            ]);

        (new ClassifyTicket($ticket->id))->handle($ai);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'category' => 'account',
            'priority' => 'high',
            'sentiment' => 'negative',
            'summary' => 'Customer cannot log in.',
            'language' => 'en',
            'ai_status' => 'done',
        ]);
    }
}
