# SmartDesk — AI Ticket Triage

> **Architecture, prototyping workflow and submission README — VERSION 2 (22 November 2026).** This document describes the **planned implementation**, not a claim that features have already been built or tested. Update every checkbox, class name, command, screenshot, and result against the actual Laravel 12 repository before submission.

## 1. Project overview

SmartDesk is a Laravel application for support-ticket submission, automatic AI triage, and agent-assisted replies. A customer can submit a ticket without an account; an authenticated support agent can review tickets, filter them, correct classifications, generate an editable reply draft, and resolve tickets. The scenario assumes approximately 300 incoming tickets per day.

**Design priorities:** understandable object-oriented code; secure input handling; reliable operation when AI fails; resource-conscious database queries; and protection of agent overrides from concurrent background jobs.

### Core requirements (planned)

- [ ] Public ticket form: name, email, subject, message (20–5,000 characters), Form Request validation, `SD-000123`-style reference.
- [ ] Database-queued classification: category, priority, sentiment, one-sentence summary (≤200 characters), language ISO code.
- [ ] Agent login and ticket dashboard: 15 tickets/page; category, priority and status filters; subject/message search.
- [ ] AI draft in ticket language, editable and saved by agent.
- [ ] Manual category and priority overrides with independent `ai`/`agent` provenance.
- [ ] AI state `pending`/`done`/`failed` and retry for failed tickets.
- [ ] Statistics: counts by category and percentage of tickets reclassified by agents.
- [ ] Reliable AI integration, fake mode, request logs, security and automated tests.

## Development methodology: iterative prototypes + verification

**Decision:** SmartDesk uses **one runnable Laravel 12 application at the repository root**, developed through successive working prototypes (P0–P5). Each prototype is a **Git commit/tag**, not a duplicated Laravel installation. Small, isolated experiments may live in `prototypes/`, but the submitted application always runs from the repository root.

This is **iterative and incremental development with V-Model-inspired verification**: each milestone defines a requirement, implements it, and verifies it. It is not a claim of using the full formal V-Model or Spiral Model.

| Milestone | Illustrative time box | Working increment | Verification evidence |
| --- | --- | --- | --- |
| P0 Foundation | Hour 0–1 | Laravel 12, database, Git, environment | App boots; migration succeeds |
| P1 Public tickets | Hour 1–3 | Form, validation, reference, queue dispatch | Form/validation/dispatch tests |
| P2 Agent dashboard | Hour 3–5 | Auth, ticket list, filters, search | Guest access, filtering and pagination tests |
| P3 AI classification | Hour 5–8 | Interface, fake/Ollama drivers, DTO, queue, retries | Valid/invalid/500 response tests |
| P4 Agent workflow | Hour 8–10 | Draft, override, AI retry, stats | Override, draft and rate-limit tests |
| P5 Release candidate | Hour 10+ | Stabilization, README, demo, submission | Fresh install, full test suite, real-AI recording |

**These hours are planning estimates, not logged actual hours.** Prioritize all required core features and tests over optional bonuses. Each stage should leave the root application runnable whenever possible.

### Prototype folder rules

- `prototypes/` is **optional** and only for small standalone proofs of concept (e.g., a memory benchmark or isolated JSON parser). Do not duplicate `vendor/`, `node_modules/`, `.env`, or entire Laravel installations there.
- `docs/iterations/P0.md` through `P5.md` record objective, implementation, verification, known risks and outcomes; record actual times, not invented times.
- `docs/decisions/` holds concise Architecture Decision Records (ADRs), including memory and concurrency decisions.
- Git tags such as `prototype/p1-ticket-form` preserve exact runnable snapshots. A Git tag is **not** a directory.
- The root `README.md` is the **single submission entry point**. Reviewers should never need to choose a prototype folder.

### Git commands after a milestone passes

```bash
git add .
git commit -m "feat: implement public ticket submission"
git tag prototype/p1-ticket-form
git push origin main
git push origin prototype/p1-ticket-form
```

Run these from the repository root after checking `git status` and ensuring `.env` and secrets are excluded. Use meaningful commits for intermediate work; create a tag only when the milestone is genuinely usable. Do not tag a milestone as complete before verification.

### Iteration log template

```markdown
# P1 — Public ticket submission
Objective: [requirement]
Changes: [classes, routes, migration, views]
Verification: [test commands and actual results]
Performance/concurrency notes: [measured facts or risks]
Known limitations: [unfinished work]
Actual time: [start/end or duration]
Outcome: [pending / passed / blocked]
```

## 2. Technology stack

| Layer | Planned technology | Why |
| --- | --- | --- |
| Backend | PHP 8.3+, **Laravel 12** | Assessment-compatible MVC, dependency injection, ORM, validation and queues |
| Local environment | Laragon on Windows 11 | PHP, Apache and MySQL local development |
| Database | MySQL 8.4 (reviewer may use supported SQLite) | Relational data, indexing, transactions |
| ORM | Laravel Eloquent | Model relationships, parameter-bound values, pagination |
| Frontend | Blade, HTML, CSS, optional Vite assets | Simple server-rendered UI with low complexity |
| Authentication | Laravel-compatible session auth / Breeze if compatible | Agent-only pages |
| Queue | Laravel **database** queue | No Redis requirement |
| AI contract | `App\Contracts\TicketAiService` | Swappable implementations |
| Offline AI | `FakeTicketAiService` | Deterministic, network-free reviewer mode |
| Real AI | Ollama local model, e.g. `qwen2.5:3b` | Local inference without external API key |
| HTTP | Laravel `Http` facade | Timeouts, retry, HTTP fakes |
| Testing | PHPUnit, `Http::fake()`, `Queue::fake()` | Repeatable tests without real AI calls |
| Version control | Git + private GitHub repository | Clear commits and submission history |

**Note:** The specific installed versions, auth package and AI model must be verified in the final repository. Ollama is a separate process and consumes RAM independently of PHP/Laravel.

## 3. Laravel concepts compared with legacy PHP and C++

| Laravel concept | Legacy PHP / C++ analogy | Responsibility |
| --- | --- | --- |
| `routes/web.php` | URL dispatch / request entry script | Map HTTP verb + path to controller method |
| Controller | PHP handler function / C++ coordinator class | Orchestrate a request; avoid putting AI HTTP calls here |
| Form Request | Validation function + typed request object | Validate and authorize input before controller action |
| Eloquent Model | Data-access object / active record | Represent table rows, relationships and queries |
| Migration | Versioned SQL schema script | Create and alter tables reproducibly |
| Seeder | Test/initial-data import script | Seed agent and sample tickets |
| Blade view | HTML template with escaped output | Render UI; `{{ }}` escapes by default |
| Job | Background work item | Run AI classification outside web response |
| Service | Business-logic class | Encapsulate provider-specific AI operations |
| Interface (`interface`) | C++ abstract contract | Define methods all AI drivers must implement |
| DTO | Typed data transfer object | Carry validated AI classification data |
| Enum | C++ `enum class` analogue | Restrict categories, priority, status and sentiment |
| Service container | Dependency injection factory | Supply interface implementation to jobs/services |
| `use` | Namespace import (not `#include`) | Refer to class by short name; Composer autoloads PHP classes |
| `::` | Class/static-style access | E.g. `Ticket::query()`; Eloquent supports dynamic static-style calls |
| `->` | Object member access | E.g. `$ticket->subject` or `$query->paginate(15)` |

`<?php` starts a PHP file. PHP has no separate C++-style `.h` header file requirement. Composer's PSR-4 autoloader normally loads classes in `app/`; do not manually `include` each model/controller.

## 4. Proposed project structure

```text
smartdesk-ai-ticket-triage/          # ONE runnable Laravel 12 root
├── app/
│   ├── Contracts/
│   │   └── TicketAiService.php          # REQUIRED interface
│   ├── DTOs/
│   │   └── TicketClassification.php     # Validated AI output
│   ├── Enums/
│   │   ├── TicketCategory.php
│   │   ├── TicketPriority.php
│   │   ├── TicketSentiment.php
│   │   ├── TicketStatus.php
│   │   └── AiStatus.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TicketController.php     # Public ticket form
│   │   │   ├── Agent/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── TicketController.php # Show, override, retry
│   │   │   │   └── TicketReplyController.php
│   │   │   └── Auth/                   # If generated by auth scaffolding
│   │   └── Requests/
│   │       ├── StoreTicketRequest.php
│   │       ├── UpdateTicketRequest.php
│   │       └── SaveReplyRequest.php
│   ├── Jobs/
│   │   └── ClassifyTicketJob.php
│   ├── Models/
│   │   ├── Ticket.php
│   │   ├── AiRequest.php
│   │   └── User.php
│   ├── Services/Ai/
│   │   ├── FakeTicketAiService.php
│   │   └── OllamaTicketAiService.php
│   └── Providers/
│       └── AppServiceProvider.php
├── docs/
│   ├── architecture/architecture.md
│   ├── iterations/                    # P0.md ... P5.md
│   └── decisions/                     # Short ADRs
├── prototypes/                        # OPTIONAL isolated experiments only
│   └── README.md
├── bootstrap/
│   ├── app.php                       # App bootstrap / middleware
│   └── providers.php                 # Provider registration
├── config/
│   └── ai.php                        # Reads AI_* environment settings
├── database/
│   ├── migrations/                   # Tickets, AI requests, queue, etc.
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── TicketSeeder.php
│   └── data/sample-tickets.csv       # 24 supplied tickets
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── tickets/create.blade.php
│   │   ├── tickets/success.blade.php
│   │   ├── agent/dashboard.blade.php
│   │   └── agent/tickets/show.blade.php
│   ├── prompts/
│   │   ├── classify.txt
│   │   ├── classify-strict.txt
│   │   └── draft-reply.txt
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   ├── web.php                        # Public and authenticated web routes
│   └── api.php                        # OPTIONAL; not needed for outbound Ollama
├── tests/
│   ├── Feature/
│   │   ├── TicketSubmissionTest.php
│   │   ├── TicketAiTest.php
│   │   ├── AgentAccessTest.php
│   │   └── DraftRateLimitTest.php
│   └── Unit/TicketClassificationTest.php
├── public/index.php                  # HTTP entry point
├── .env                               # Local secrets; NEVER commit
├── .env.example                       # Safe configuration template
├── composer.json
├── artisan
└── README.md
```

This tree is a **target layout**, not a claim all files exist. Do not create six complete Laravel copies in `prototypes/`; each full working version is captured by Git history/tags. `routes/api.php` is optional and may not be installed in a new Laravel 12 project. **Outbound** Ollama HTTP requests belong in `Services/Ai`, not `routes/api.php`. Laravel's `routes/web.php` handles Blade form actions and agent sessions.

## 5. Object-oriented architecture: every planned class

### Domain and persistence

| Class | Role | Key behavior |
| --- | --- | --- |
| `App\Models\Ticket` | Eloquent model | Ticket fields, `belongsTo(User::class, 'agent_id')`, `hasMany(AiRequest::class)`; whitelist mass-assignable fields |
| `App\Models\User` | Authenticated agent | Login, `hasMany(Ticket::class, 'agent_id')` |
| `App\Models\AiRequest` | AI call log | Type, model, duration, optional token counts, success, ticket relation; never store secrets |
| `TicketCategory` | Backed enum | `billing`, `technical`, `account`, `feature_request`, `other` |
| `TicketPriority` | Backed enum | `low`, `medium`, `high`, `urgent` |
| `TicketSentiment` | Backed enum | `positive`, `neutral`, `negative` |
| `TicketStatus` | Backed enum | `new`, `open`, `resolved` |
| `AiStatus` | Backed enum | `pending`, `done`, `failed` |
| `TicketClassification` | DTO | Category, priority, sentiment, summary ≤200 chars, valid ISO language; reject invalid AI output |

### HTTP layer

| Class | Role | Key behavior |
| --- | --- | --- |
| `TicketController` (public) | Customer form | `create()`, `store()`; persist validated ticket, queue classification, redirect to reference |
| `StoreTicketRequest` | Public form validation | Name/email/subject/message, message 20–5,000 characters |
| `DashboardController` | Agent overview | Filtered, paginated list and category/override stats |
| `Agent\TicketController` | Agent ticket management | `show()`, `update()` for overrides, `retry()` for failed AI jobs |
| `UpdateTicketRequest` | Override validation | Allowed category/priority values and authorization |
| `TicketReplyController` | Reply workflow | `generateDraft()` and `save()`; cache, throttle, persist edited text |
| `SaveReplyRequest` | Reply validation | Validate edited reply body |

### AI layer

| Class | Role | Key behavior |
| --- | --- | --- |
| `TicketAiService` | **Required interface** | `classify(Ticket $ticket): TicketClassification`, `draftReply(Ticket $ticket): string` |
| `FakeTicketAiService` | Offline implementation | Deterministic valid classification and draft, no network |
| `OllamaTicketAiService` | Real implementation | Laravel `Http::`, prompts, JSON parsing, retry, response validation and logs |
| `ClassifyTicketJob` | Async work | Resolve interface via dependency injection, call `classify`, persist results or failed state |
| `AppServiceProvider` | Service-container binding | Bind `TicketAiService::class` to Fake or Ollama based on `config('ai.driver')` |

**Important distinction:** using multiple classes inside `store()` is **object collaboration/delegation**, not an interface. Encapsulation protects class state/implementation; an interface defines a contract; dependency injection supplies the chosen implementation; polymorphism lets fake and real AI be used through the same contract.

### Interface example

```php
namespace App\Contracts;

use App\Models\Ticket;
use App\DTOs\TicketClassification;

interface TicketAiService
{
    public function classify(Ticket $ticket): TicketClassification;
    public function draftReply(Ticket $ticket): string;
}
```

Both AI drivers must implement these signatures. A human agent's override is **not** an AI provider and will be handled through a separate authorized controller action, not by pretending a human is a network AI driver.

## 6. Data flow and routes

```text
PUBLIC CUSTOMER
GET /tickets/create → TicketController@create → Blade form
POST /tickets      → StoreTicketRequest → TicketController@store
                   → Ticket::create() → DB transaction/reference
                   → ClassifyTicketJob::dispatch() → redirect to success

BACKGROUND WORKER
ClassifyTicketJob → TicketAiService interface
                  → Fake OR Ollama implementation
                  → validate DTO → update ticket + AI status
                  → record ai_requests

AUTHENTICATED AGENT
GET /agent/tickets         → DashboardController@index
GET /agent/tickets/{ticket} → Agent/TicketController@show
PATCH /agent/tickets/{ticket} → Agent/TicketController@update
POST /agent/tickets/{ticket}/retry → Agent/TicketController@retry
POST /agent/tickets/{ticket}/draft → TicketReplyController@generateDraft
POST /agent/tickets/{ticket}/reply → TicketReplyController@save
```

These paths are **proposed**, not final. Use named routes, `auth` middleware for all agent pages, authorization for modifications, CSRF tokens for state-changing forms, and throttles on public ticket submission and draft generation.

### Example: controller collaboration

```php
public function store(StoreTicketRequest $request)
{
    $ticket = Ticket::create($request->validated());
    ClassifyTicketJob::dispatch($ticket);

    return redirect()->route('tickets.success', $ticket);
}
```

This is a **teaching sketch**, not the final complete controller. Real code must generate the ticket reference safely, set defaults, define the success route, protect mass assignment, and ensure the job is queued **after the transaction commits** if a transaction is used.

## 7. Database plan

### `tickets` (proposed)

- `id` (primary key), `reference` (unique), `name`, `email`, `subject`, `message`
- `category`, `category_source` (`ai`/`agent`), `priority`, `priority_source` (`ai`/`agent`)
- `sentiment`, `summary`, `language`, `status`, `ai_status`
- `reply_draft` (optional), `reply_body` (optional), `agent_id` (nullable FK), timestamps

### `ai_requests` (required)

- `id`, `ticket_id` (FK), `type` (`classify`/`draft`), `model`, `duration_ms`, optional token counts, `success`, timestamps
- Diagnostic invalid responses can be recorded in a **restricted, size-limited diagnostic field or secure application log** without API keys; avoid exposing customer data in UI or logs unnecessarily.

### Additional tables

- `users` (seeded agent); `jobs` and related database queue tables; cache/session tables if configured; migration tracking table.

### Indexes and queries

- Unique index on `reference`; useful indexes on filter columns (consider actual query patterns and cardinality).
- Use indexed primary key for ticket detail and stable ordering (`created_at`, `id`) for pagination.
- Keyword search using `LIKE` on subject/message is acceptable for the small MVP; document that `%keyword%` may scan rows and may need full-text search at scale.
- Use Eloquent relationships and eager loading only when the view needs related records.

## 8. Performance and memory efficiency

These are **design intentions**, not benchmark results.

1. **Bounded pagination:** `Ticket::query()->select([...])->orderByDesc('id')->paginate(15)` instead of `Ticket::all()` on the dashboard. Only fetch fields displayed in the list.
2. **Avoid N+1:** eager-load required relationships via `with('agent:id,name')` when showing agent names; do not load unrelated data.
3. **Database-level filtering:** apply `where`, validated filters and keyword conditions **before** `paginate()`; avoid fetching all records and filtering in PHP arrays.
4. **Background processing:** queue AI classification using `QUEUE_CONNECTION=database` to keep the public HTTP response responsive.
5. **Controlled concurrency:** begin with one queue worker for predictable local AI load; add concurrency only after measurement and correctness checks.
6. **Small AI payloads:** send only necessary ticket fields, cap response size where practical, keep prompts focused, and avoid dumping entire histories into AI context.
7. **Draft cache:** cache generated drafts per ticket for 10 minutes; invalidate when relevant ticket content changes. Rate-limit draft requests to 10/minute/agent.
8. **Memory monitoring:** Laravel worker `--memory` is a restart threshold for the **PHP worker**, not a hard RAM cap and not a limit on Ollama. Ollama's model RAM must be considered separately.
9. **Seed/import efficiency:** use a streaming CSV reader (`fgetcsv`) or bounded chunks rather than loading a large CSV entirely into an array. Supplied 24-row CSV is small; this is a scalable coding habit.
10. **Measure before optimizing:** compare query count, latency and peak PHP memory using profiling/logging; do not claim a specific percentage improvement without a benchmark.

### Example bounded query

```php
$tickets = Ticket::query()
    ->select(['id', 'reference', 'subject', 'category', 'priority', 'status', 'created_at'])
    ->orderByDesc('id')
    ->paginate(15);
```

## 9. Concurrency and race-condition prevention

**Risk:** AI classification begins; an agent manually changes priority to `urgent`; a slower AI result returns `low` and overwrites the agent's decision.

**Policy:** agent override wins. Category and priority provenance must be handled **independently**.

Recommended implementation:

1. Set ticket `ai_status=pending` when queued.
2. On manual override, write the new value and set that field's `*_source='agent'` atomically.
3. On AI completion, update each classification field only if its source is **not** `agent`. Prefer conditional database updates or a short transaction with row locking, depending on how fields and statuses are coordinated.
4. Use a guarded state transition or unique processing token to prevent duplicate/retried older jobs from replacing newer results. `ShouldBeUnique` alone is not a complete solution to stale updates.
5. Dispatch jobs after DB commit (`afterCommit()` where appropriate) so a worker never reads a ticket that has not committed.
6. Keep transactions short; do **not** hold a database row lock while waiting up to 30 seconds for Ollama.
7. On AI failure, preserve the ticket and agent changes; mark AI state failed and permit an authorized retry.

**Atomic conditional update example (illustrative):**

```php
Ticket::query()
    ->whereKey($ticketId)
    ->where('priority_source', '!=', 'agent')
    ->update([
        'priority' => $classification->priority->value,
        'priority_source' => 'ai',
    ]);
```

If source columns are nullable, handle `NULL` explicitly or give them a non-null default; SQL `NULL != 'agent'` is not true. The final implementation must also coordinate job versioning and category updates.

## 10. AI reliability, security and cost control

- **Interface boundary:** controllers/jobs never call Ollama HTTP directly; provider code lives behind `TicketAiService`.
- **HTTP:** Laravel `Http::` with timeout **≤30 seconds**, retries for **429/5xx** up to **two additional attempts**, with backoff; avoid retrying ordinary validation/client errors blindly.
- **Structured output:** instruct the model to return JSON only. Parse into `TicketClassification` and reject invalid categories, priorities, sentiments, language codes or summaries >200 characters.
- **Invalid response:** retry once with stricter prompt; if still invalid, set `ai_status=failed` and record safe diagnostic information.
- **Prompt injection:** system/developer instructions are separate from untrusted customer text. Customer text is data, not authority. DTO validation prevents unsupported output values; manual agent override is always explicit.
- **Prompt files:** `resources/prompts/classify.txt`, `classify-strict.txt`, `draft-reply.txt`.
- **Config:** `AI_DRIVER`, `AI_MODEL`, `AI_BASE_URL`, `AI_API_KEY`, `AI_TIMEOUT`, `AI_MAX_RETRIES` read through `config/ai.php`; never commit secrets.
- **Logs:** record AI request type, ticket ID, model, duration, token counts when available and success. No API keys.
- **Caching:** 10-minute draft cache per ticket and relevant content/version; avoid serving stale drafts after ticket changes.
- **Rate limiting:** public submission **5/min/IP**, draft generation **10/min/agent**.
- **Browser security:** CSRF on forms, Blade escaped output, authentication and authorization on agent actions.
- **SQL injection:** use Eloquent/Query Builder parameter binding for **values**; whitelist dynamic sort columns and directions. OOP alone does not prevent SQL injection.

## 11. Setup instructions (to verify against final implementation)

### Prerequisites

PHP 8.2+, Composer 2, Node.js/npm 20+, Git, MySQL 8 or SQLite, and optional Ollama for real AI demo. Use **Laravel 12**, not Laravel 13, for this assessment.

### Initial setup

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

In a **second terminal**:

```bash
php artisan queue:work
```

For an entirely offline review, set in `.env`:

```dotenv
APP_NAME=SmartDesk
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartdesk
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
CACHE_STORE=database
AI_DRIVER=fake
AI_MODEL=
AI_API_KEY=
AI_BASE_URL=
AI_TIMEOUT=30
AI_MAX_RETRIES=2
```

**Reviewer note:** The example above is a **local MySQL example**, not a universal setup. For SQLite, change `DB_CONNECTION=sqlite`, ensure the SQLite database file exists, and run migrations. The checked-in `.env.example` must be usable without real credentials. Configure fake driver without an API key.

### Real Ollama demo (planned)

```bash
ollama list
ollama run qwen2.5:3b
```

Set `AI_DRIVER=ollama`, the implemented model name, and appropriate local Ollama base URL in `.env`. Confirm the service can reach Ollama before recording. Do not show `.env` secrets in the video.

### Agent account

- **Seeded email:** `[ADD AFTER IMPLEMENTATION]`
- **Seeded password:** `[ADD AFTER IMPLEMENTATION; development demo credentials only]`

### Run tests

```bash
php artisan test
```

**Actual test result:** `[NOT YET VERIFIED]`

## 12. Test plan

- [ ] Valid AI classification saved to ticket.
- [ ] Invalid JSON → one stricter retry → failed state.
- [ ] Invalid enum value (e.g. `priority=critical`) rejected.
- [ ] HTTP 500 retried/handled without breaking ticket.
- [ ] Ticket submission dispatches job (`Queue::fake()`).
- [ ] Guest cannot access agent pages.
- [ ] Draft endpoint rate-limited.
- [ ] Public form throttled 5/min/IP.
- [ ] Human priority/category overrides survive delayed AI result.
- [ ] Seed 24 multilingual sample tickets; include prompt-injection cases.
- [ ] `AI_DRIVER=fake` works without network or API key.

All tests that simulate AI HTTP must use `Http::fake()`; no real API calls in the test suite.

## 13. Submission and evidence

- [ ] Root Laravel project is the only required runnable application; `prototypes/` contains no redundant full-project copies.
- [ ] Iteration logs and milestone tags reflect actual verified progress (not just planned work).

- [ ] Repository private; invite GitHub user `digitalinnovation2026` as collaborator.
- [ ] Small, meaningful Git commits showing development progression.
- [ ] `.env` ignored; `.env.example` committed with empty keys.
- [ ] Fresh clone: `composer install`, `npm install && npm run build`, `php artisan migrate --seed`, `php artisan queue:work`.
- [ ] `AI_DRIVER=fake` works without an API key.
- [ ] `php artisan test` passes.
- [ ] 2–3-minute screen recording demonstrates submit → real AI classification → editable reply.
- [ ] Demo recording link: `[ADD LINK]`.
- [ ] Bonus features actually implemented: `[NONE CONFIRMED YET]`.
- [ ] Total development hours: `[RECORD ACTUAL HOURS]`.
- [ ] AI coding assistance disclosure: `[DESCRIBE HOW CHATGPT/OTHER TOOLS WERE USED, WHAT WAS REVIEWED AND TESTED]`.

## 14. Trade-offs and future improvements

**Intentional MVP choices:** Blade instead of a heavy SPA; database queue instead of Redis; one local Ollama model and a deterministic fake; no separate inbound API unless needed; simple ORM queries instead of a repository layer that duplicates Eloquent.

**Possible future improvements:** measure query performance and memory; optimize indexes from real query plans; full-text search for large ticket volumes; stronger AI job idempotency/versioning; better authorization policies and audit trails; request correlation/observability; Docker; provider failover; relevant-FAQ retrieval (simple RAG); streaming drafts; embedding-based similar tickets. These are **not claimed as implemented**.

## 15. Interview explanation: 60-second architecture summary

> SmartDesk uses Laravel 12 with a Blade frontend and Eloquent models backed by MySQL. The public form is validated by a Form Request, then a controller persists the ticket and dispatches a database-queued classification job. The job depends on the `TicketAiService` interface, which can be fulfilled by a fake implementation for offline testing or an Ollama implementation for real inference. AI output is parsed into a validated DTO and errors do not block ticket submission. Agent pages are authenticated, support pagination and filtering, and allow manual overrides. I designed the data updates so a late AI response cannot overwrite an agent's decisions. I also use bounded queries, selective columns, background work and caching to keep resource use predictable. Tests fake HTTP and queue interactions so they are repeatable without an AI service.

**Use this script only if each statement matches the completed code.** The reviewer may request a live change; understanding the actual implementation is more important than memorizing this paragraph.

---

**Source:** Alpro SmartDesk assessment brief and submission checklist supplied for the take-home exercise. The iterative milestone workflow, folder organization, performance strategies and concurrency design are proposed engineering decisions, not explicit assessment requirements. This README is a preparatory draft and must be reconciled with the actual code before submission.
