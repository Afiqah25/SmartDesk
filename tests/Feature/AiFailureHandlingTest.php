<?php

namespace Tests\Feature;

use App\Contracts\TicketAiService;
use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiFailureHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_ai_marks_ticket_as_failed(): void
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
            ->andThrow(new \RuntimeException('Ollama unavailable'));

        $this->app->instance(TicketAiService::class, $ai);

        $job = new ClassifyTicket($ticket->id);

        try {
            $this->app->call([$job, 'handle']);
        } catch (\RuntimeException $e) {
            $this->assertSame('Ollama unavailable', $e->getMessage());
        }

        // Simulate queue worker exhausting all retries.
        $job->failed(new \RuntimeException('Ollama unavailable'));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'ai_status' => 'failed',
        ]);
    }
}
