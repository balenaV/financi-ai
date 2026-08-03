This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Project

financiai — a Brazilian Portuguese personal finance web app. Laravel 13, PHP 8.5, PostgreSQL (schema financiai), Blade, Tailwind CSS 4, jQuery, Chart.js. Covers accounts, transactions, transfers, debts, credit cards, investments, budgets, goals, and reports, with strict per-user data isolation.

All UI copy, validation messages, and commit-facing docs are in Portuguese (pt_BR). Match that when writing user-facing strings.

Development environment

Development happens inside WSL2 (Linux), running the project via Docker Compose (PHP-FPM, Nginx, PostgreSQL, Node). Use native Linux/bash commands and syntax — not PowerShell. There is no requirement to have PHP/Composer/Node/Postgres installed on the WSL distro itself — everything runs through docker compose run. Keep the repo on the Linux filesystem (e.g. under ~/, not /mnt/c/...) for acceptable Docker volume I/O performance.

bash
docker compose up -d                                   # start stack (http://localhost:8080)
docker compose run --rm app php artisan test            # PHPUnit
docker compose run --rm app ./vendor/bin/pint --test    # lint check (Pint)
docker compose run --rm app ./vendor/bin/pint           # lint fix
docker compose run --rm --user root app npm run build   # build assets (Vite)
docker compose run --rm --user root app npm install     # install JS deps (needs root for volume perms)

Run a single PHPUnit test:

bash
docker compose run --rm app php artisan test --filter=TestClassName
docker compose run --rm app php artisan test tests/Feature/TransactionWorkflowTest.php

Playwright E2E tests (tests/E2E/*.spec.js) run against a live server:

bash
npm run test:e2e

CI runs Pint, PHPUnit with coverage, npm run build, then Playwright (chromium + mobile/Pixel 7 projects) against a served build — see .github/workflows/ci.yml.

If assets/DB state look stale after container/dependency changes:

bash
docker compose run --rm --user root app npm run build
docker compose run --rm app php artisan optimize:clear

Overriding ports (e.g. if 8080/5432 are already taken on the WSL host):

bash
APP_PORT=8081 docker compose up -d
DB_FORWARD_PORT=5433 docker compose up -d
Branching and deploy
dev = development/staging. All work starts here.
main = production only. Reached exclusively via PR from dev, merged without extra commits.
Urgent fixes branch from main as hotfix/*, then get back-ported to dev.
Full detail: docs/branching.md.

Production hosting has been migrated off Vercel + Supabase to a self-managed Hostinger VPS. Both staging and production run on the same VPS, as fully separate Docker Compose projects (separate clones, .env files, containers, and Postgres databases — nothing is shared between them).

	Staging	Production
Branch	dev	main
Path on VPS	/var/www/financiai-staging	/var/www/financiai
Docker Compose project (-p)	financiai-staging	financiai-prod
Internal Nginx port	127.0.0.1:8081	127.0.0.1:8080
Domain	staging.financiai.cloud	financiai.cloud, www.financiai.cloud
Database	financiai_staging (own container)	financiai_prod (own container)

Deploy is via GitHub Actions over SSH (appleboy/ssh-action), defined in .github/workflows/deploy.yml:

Push to dev → deploys to staging automatically.
Push to main → deploys to production, gated behind a manual approval (GitHub production environment with required reviewers).

Both deploy jobs run, on the VPS: git pull, docker compose -p <project> -f compose.prod.yaml up -d --build, composer install --no-dev --optimize-autoloader, php artisan migrate --force, then config:cache/route:cache/view:cache.

Related workflows:

.github/workflows/backup-vps.yml — weekly pg_dump of the production database, run over SSH against the VPS's Postgres container, uploaded as a workflow artifact.
.github/workflows/migrate-vps.yml — manually-triggered (workflow_dispatch), lets you run php artisan migrate --force against staging or production independently of a full deploy.

Host-level Nginx (outside Docker, installed via apt) reverse-proxies each domain to its container's internal port and terminates TLS (Let's Encrypt via Certbot). The app's own web container (Nginx inside Docker, defined in compose.prod.yaml) only listens on 127.0.0.1, never exposed directly to the internet.

compose.prod.yaml is the same file for both environments (versioned in the repo); what differs between staging and production is only the .env (never versioned) and the exposed port. Don't hardcode staging- or production-specific values into compose.prod.yaml — those belong in .env.

Legacy Vercel/Supabase artifacts (api/index.php entrypoint, vercel-php runtime, vercel.json, docs/deploy-vercel.md) are being removed as they're found; if you encounter references to them, they're stale and safe to delete unless told otherwise. Don't assume Vercel-specific constraints (e.g. /tmp-only writable storage, DB_POOL_MODE=transaction for Supabase's pooler) apply anymore — production now runs standard Laravel filesystem storage and DB_POOL_MODE=session against self-hosted Postgres, same as local dev.

OAuth (Google / GitHub login)
Google: a single OAuth Client ID/Secret is shared across local, staging, and production — each environment's callback URL ({APP_URL}/auth/google/callback) is registered as an additional Authorized redirect URI on the same Google Cloud OAuth client.
GitHub: each environment requires its own separate OAuth App (financiai-local, financiai-staging, financiai), since GitHub only allows a single callback URL per OAuth App. Never reuse one environment's GitHub Client ID/Secret in another environment's .env.
Architecture
app/
├── Http/Controllers/   # thin — HTTP + authorization only
├── Http/Requests/      # all validation and input normalization
├── Http/Middleware/    # EnsureRegistrationEnabled, AuditUserAction
├── Models/             # Eloquent entities, all business tables have user_id
├── Policies/           # authorization; most extend OwnedResourcePolicy
├── Enums/              # domain states (TransactionStatus, DebtStatus, ...)
├── Services/           # financial rules, atomic transactions
└── Support/Money.php   # decimal-safe monetary math (bcmath, minor units)

Controllers → Requests → Services → Models is the standard flow: controllers stay thin and delegate validation to Form Requests and financial logic/DB transactions to app/Services/*. Look in Services/ first when changing how money moves (transfers, installments, credit card billing, budgets, investments).

Authorization: most policies are one-liners extending OwnedResourcePolicy (app/Policies/OwnedResourcePolicy.php), which checks model.user_id === user.id for view/update/delete. Every business table is scoped to user_id; queries should go through the user's relations (e.g. $user->accounts(), $user->transactions()), not global model queries, to keep isolation correct. SecurityIsolationTest and ProductionReadinessTest in tests/Feature guard this — check them when touching cross-user access paths.

Money: never use floats for currency. App\Support\Money normalizes locale input (comma/dot), converts to/from integer minor units (cents) via bcmath, and splits installment amounts without rounding drift (Money::split). Use it for any monetary calculation or parsing.

Account balance (see README "Banco e regras de saldo" for full rules): saldo inicial + receitas pagas − despesas pagas + transferências recebidas − transferências enviadas. Pending/cancelled transactions never affect balance. Transfers create two linked transactions inside one DB transaction (TransferService). Credit card purchases attach to the correct bill by closing/due days; the bank balance is only reduced when the bill is paid, not at purchase time, to avoid double-counting (CreditCardService).

Audit: the audit middleware (AuditUserAction) logs every non-GET authenticated request to AuditLog (event type, route, resource id, status). Applied at the route-group level in routes/web.php.

Registration gating: ALLOW_REGISTRATION env flag + EnsureRegistrationEnabled middleware — when disabled, registration routes 404 and the UI hides the link. Relevant when touching routes/auth.php or Breeze registration views.

Database: PostgreSQL only, all domain + framework tables live in a non-default finance schema (search_path = finance,public, set via DB_SCHEMA). All environments (local, staging, production) use DB_POOL_MODE=session against a plain, self-hosted Postgres container — no pooler, no transaction pool mode quirks to worry about anywhere anymore.

Frontend: server-rendered Blade + Tailwind CSS 4, jQuery for interactivity, Chart.js for graphs. JS is split into small modules under resources/js/modules/ (charts.js, forms.js, ui.js), entry point resources/js/app.js, built with Vite (laravel-vite-plugin). No SPA framework or build-time component system — views are Blade components in resources/views/components/.

Design tokens: color/spacing tokens and their rationale (creme/black/green brand direction, WCAG-driven deviations from the original mockup values) are documented in docs/design-system.md. Treat it as the source of truth before hand-picking colors for new UI — several dark-mode values intentionally diverge from the reference mockup for contrast reasons.

Testing conventions
tests/Unit — pure logic (e.g. MoneyTest, InstallmentServiceTest), no DB.
tests/Feature — HTTP/Eloquent flows: auth, per-user isolation (SecurityIsolationTest), validation, balances, transfers, installments, debts, investments, budgets, goals, dashboard, reports, production-readiness checks (ProductionReadinessTest).
tests/E2E — Playwright, desktop (chromium) + mobile (Pixel 7) projects, drives the real served app.
PHPUnit runs against in-memory SQLite (phpunit.xml), not Postgres — don't rely on Postgres-only SQL (schemas, bcmath-adjacent SQL functions, etc.) inside code paths exercised by these tests.
Regra crítica — NUNCA VIOLAR

.env.example deve conter APENAS nomes de variáveis, todas vazias (campo=). NUNCA copiar valores de dentro de um .env real para o .env.example, mesmo como "exemplo" ou "referência". Se precisar mostrar formato, usar placeholder genérico tipo your-value-here, nunca um valor funcional.

Isso vale para qualquer arquivo versionado, não só .env.example — nunca commitar segredos reais (senhas, chaves de API, tokens, client secrets) em nenhum arquivo do repositório.
