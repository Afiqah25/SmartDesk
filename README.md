# SmartDesk — AI Ticket Triage

**Laravel 12 | PHP 8.3 | MySQL | Ollama | Blade | Database Queue**

SmartDesk is a web-based support ticket management application developed as a programming assessment project.

It allows customers to submit support tickets, uses AI to classify incoming requests, and provides an authenticated dashboard for support agents to manage tickets, override AI decisions, and prepare AI-assisted replies.

## 1. Project Overview

SmartDesk demonstrates the integration of Laravel's MVC architecture, object-oriented programming, database operations, asynchronous job processing, and local large language models (LLMs).

The application is designed around four principles:

- **Maintainability:** Separate controllers, models, services, and background jobs.
- **Reliability:** Keep customer ticket submission independent of AI processing.
- **Human oversight:** Allow agents to review and override AI classifications.
- **Resource efficiency:** Use server-rendered Blade views, database pagination, and background processing.

## 2. Technology Stack

| Component | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.3 |
| Database | MySQL |
| Frontend | Blade, HTML, CSS |
| AI Engine | Ollama |
| AI Model | Qwen 2.5 3B |
| Background Processing | Laravel Database Queue |
| Authentication | Laravel Session Authentication |
| Testing | PHPUnit / Laravel Feature Tests |
| Development Environment | Laragon, Windows 11 |
| Version Control | Git and GitHub |

## 3. Implemented Features

### 3.1 Public Ticket Submission

Customers can submit support tickets without creating an account.

The form collects:

- Customer name
- Email address
- Subject
- Message

Laravel Form Request validation checks the submitted information.

Each successfully submitted ticket receives a reference number in the format:

`SD-000001`

After submission, an AI classification job is dispatched to the database queue.

### 3.2 AI Ticket Classification

SmartDesk integrates a locally hosted Ollama model to classify support tickets.

The classification includes:

| Field | Possible Values |
|---|---|
| Category | billing, technical, account, feature_request, other |
| Priority | low, medium, high, urgent |
| Sentiment | positive, neutral, negative |
| Summary | Short AI-generated ticket summary |
| Language | Language code, such as en or ms |

The application uses the `TicketAiService` interface to separate AI operations from controllers and jobs.

Two service implementations are available:

- `FakeTicketAiService` — provides simulated AI responses.
- `OllamaTicketAiService` — communicates with a local Ollama language model.

The interface currently returns classification results as a PHP associative array.

### 3.3 Background Job Processing

AI classification is handled by:

`app/Jobs/ClassifyTicket.php`

Laravel's database queue allows customer tickets to be saved without waiting for the language model to complete processing.

The AI processing status can be:

- `pending`
- `done`
- `failed`

The classification job is configured for up to three queue attempts.

When all attempts fail, the job's failure callback updates the ticket's AI status to `failed`.

### 3.4 Agent Authentication and Dashboard

Support agents can log in to a protected dashboard.

The dashboard provides:

- Ticket listing and pagination
- Ticket category filtering
- Priority filtering
- Status filtering
- Subject and message search
- Ticket statistics
- AI classification information

Ticket statuses include `new`, `open`, and `resolved`.

Guest users cannot access protected agent pages.

### 3.5 Human Agent Override

Agents can manually update ticket categories and priorities.

The application records whether the classification comes from AI or a human agent using:

- `category_source`
- `priority_source`

The background classification job checks these values before saving AI results, reducing the risk of overwriting an agent's manual decisions.

### 3.6 AI-Assisted Reply

Agents can generate a draft reply using Ollama.

The generated reply can be reviewed, edited, and saved by the agent.

AI-generated replies are intended to assist support agents, not replace human review.

### 3.7 Dashboard Statistics

The dashboard includes operational information such as:

- Total ticket counts
- Ticket status counts
- AI category distribution
- Percentage of tickets manually reclassified

These statistics help agents monitor incoming support requests.

## 4. Application Architecture

SmartDesk follows Laravel's Model-View-Controller (MVC) architecture.

### Important Components

| Component | Responsibility |
|---|---|
| `Ticket.php` | Ticket database model |
| `TicketController.php` | Public ticket submission |
| `StoreTicketRequest.php` | Customer form validation |
| `DashboardController.php` | Agent dashboard |
| `ClassifyTicket.php` | Queued AI classification |
| `TicketAiService.php` | AI service interface |
| `FakeTicketAiService.php` | Simulated AI implementation |
| `OllamaTicketAiService.php` | Real Ollama integration |
| `AppServiceProvider.php` | AI dependency injection |

### Ticket Processing Workflow

```text
Customer
   |
   v
Public Ticket Form
   |
   v
Laravel Validation
   |
   v
MySQL Database
   |
   v
Database Queue
   |
   v
ClassifyTicket Job
   |
   v
TicketAiService Interface
   |
   +---- FakeTicketAiService
   |
   +---- OllamaTicketAiService
                |
                v
         AI Classification
                |
                v
          Ticket Updated
                |
                v
          Agent Dashboard
                |
                v
       Human Review / Override
                |
                v
         AI Draft Reply
                |
                v
          Agent Saves Reply
```

## 5. Installation

### Prerequisites

- PHP 8.2 or later
- Composer
- MySQL
- Git
- Ollama for real AI classification
- Node.js and npm if frontend assets require building

### Step 1 — Clone Repository

```bash
git clone https://github.com/Afiqah25/SmartDesk.git
cd SmartDesk
```

### Step 2 — Install Dependencies

```bash
composer install
```

If required by the frontend configuration:

```bash
npm install
npm run build
```

### Step 3 — Configure Environment

Copy the environment example:

```bash
cp .env.example .env
```

On Windows Command Prompt:

```cmd
copy .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the MySQL connection in `.env`:

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
```

Create a MySQL database named `smartdesk` before running migrations.

The database credentials above are examples for a local development environment. Use the appropriate credentials for your own installation.

### Step 4 — Run Migrations

```bash
php artisan migrate
```

If the repository contains the required agent seed data, run:

```bash
php artisan db:seed
```

### Step 5 — Start Laravel

```bash
php artisan serve
```

Open:

`http://127.0.0.1:8000`

### Step 6 — Start Queue Worker

Open a second terminal:

```bash
php artisan queue:work database --tries=3 --timeout=90
```

Keep the queue worker running while testing AI classification.

## 6. Ollama Setup

Install Ollama from:

https://ollama.com

Download the language model:

```bash
ollama pull qwen2.5:3b
```

Verify the model:

```bash
ollama list
```

The local Ollama service must be running for real AI classification and draft generation.

The application uses `OllamaTicketAiService` through Laravel dependency injection.

For offline development or testing, `FakeTicketAiService` provides an alternative implementation.

**Configuration note:** The active AI service binding is defined in `app/Providers/AppServiceProvider.php`. Switching between fake and real AI may require updating that binding according to the implementation.

## 7. Agent Login

The application includes an authenticated agent dashboard.

**Development agent email:** `agent@smartdesk.test`

**Development password:** Set or verify the password using the local database seeder.

The demo password is intentionally not published in this README.

The agent dashboard is accessible at:

`http://127.0.0.1:8000/agent/dashboard`

Authentication is required before accessing agent ticket information.

## 8. Automated Testing

Laravel feature tests are included to verify important application behavior.

### Tests Implemented

| Test | Purpose |
|---|---|
| `TicketSubmissionTest` | Valid submission, validation, and job dispatch |
| `AgentAuthenticationTest` | Agent access and guest restrictions |
| `AiFailureHandlingTest` | Failed AI job updates ticket status |
| `AiClassificationJobTest` | AI classification persistence, if completed and verified |

The following tests have been observed passing during development:

- Ticket submission: 2 tests passed
- Agent authentication: 3 tests passed
- AI failure handling: 1 test passed

Run the complete test suite using:

```bash
php artisan test
```

Additional testing is recommended for real queue retries, invalid AI responses, rate limiting, and concurrent updates.

## 9. Security and Reliability

The application incorporates several security and reliability practices:

- Laravel Form Request validation
- Session-based authentication for agent pages
- CSRF protection through Laravel web forms
- Blade output escaping
- Eloquent database operations
- Database-backed asynchronous jobs
- Queue failure handling
- Manual classification provenance tracking
- Environment secrets excluded through `.gitignore`

The application is intended as an assessment MVP rather than a production-hardened support platform.

Further production work should include stronger authorization policies, atomic concurrent updates, complete AI response validation, request throttling verification, and protection against enumeration of public ticket success URLs.

## 10. Performance Considerations

The application uses several lightweight architectural choices:

1. **Blade templates:** Avoid the complexity of a separate frontend SPA.
2. **Database pagination:** Retrieve manageable sets of tickets.
3. **Asynchronous classification:** Avoid blocking customer form submission during AI processing.
4. **Local Ollama inference:** Run AI without a paid external API.
5. **Service abstraction:** Keep AI provider logic separate from application controllers.

No formal performance benchmark has been conducted.

## 11. Known Limitations and Future Improvements

Potential improvements include:

- Stronger schema validation for AI responses
- Atomic protection against concurrent AI and agent updates
- Complete AI request audit logging
- Additional rate-limiting and HTTP failure tests
- More comprehensive ticket seeding
- Improved retry controls for failed classifications
- Production-ready authentication and authorization
- FAQ-based retrieval-augmented generation (RAG)
- Containerized deployment
- Monitoring and performance benchmarking

These items are future improvements and are not claimed as completed features.

## 12. Development Approach

SmartDesk was developed incrementally:

| Phase | Focus |
|---|---|
| P0 | Laravel environment and database |
| P1 | Public ticket submission |
| P2 | Agent dashboard and authentication |
| P3 | Ollama classification and background queue |
| P4 | Human override and AI draft reply |
| P5 | Testing, stabilization, documentation and GitHub submission |

Each phase focused on delivering a working part of the application before progressing to the next.

## 13. AI-Assisted Development Disclosure

ChatGPT was used as a coding assistant during the development of SmartDesk.

Assistance included:

- Explaining Laravel architecture and concepts
- Generating and reviewing PHP code
- Troubleshooting implementation errors
- Designing automated tests
- Improving the user interface
- Preparing documentation

The application was configured, executed, debugged, and tested in a local Laravel environment. AI-generated suggestions were integrated through an iterative development workflow.

Ollama is separately used as the application's local AI inference engine for ticket classification and draft generation.

## 14. Demo and Submission

**GitHub Repository:**

https://github.com/Afiqah25/SmartDesk

**Demo Video:** To be added.

The demonstration should cover:

1. Customer ticket submission
2. Background AI classification
3. Agent login and dashboard
4. Manual classification override
5. AI-generated editable reply

## 15. Conclusion

SmartDesk demonstrates a practical Laravel-based support workflow that combines traditional web application development with local AI integration.

The project emphasizes clear separation of responsibilities, asynchronous processing, human oversight, and maintainable object-oriented code.

The result is a working assessment MVP with opportunities for further reliability, security, and performance improvements.

---

**Project:** SmartDesk — AI Ticket Triage  
**Framework:** Laravel 12  
**AI Integration:** Ollama Qwen 2.5 3B  
**Development Date:** November 2026
