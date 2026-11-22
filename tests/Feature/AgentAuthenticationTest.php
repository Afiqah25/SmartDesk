<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/agent/dashboard')
            ->assertRedirect('/login');
    }

    public function test_authenticated_agent_can_access_dashboard(): void
    {
        $agent = User::factory()->create();

        $this->actingAs($agent)
            ->get('/agent/dashboard')
            ->assertOk();
    }

    public function test_guest_cannot_access_ticket_details(): void
    {
        $this->get('/agent/tickets/1')
            ->assertRedirect('/login');
    }
}
