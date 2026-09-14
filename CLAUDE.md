# CLAUDE.md

Guidance for Claude Code (and the project's `.claude/agents/` subagents) when working in this repository.

## Project

**english-tutor** ("EnTutor") — a 6-month (180-day) structured spoken-English learning app, built on the official [Laravel React starter kit](https://laravel.com/docs/starter-kits). Two roles: **learner** (works through months → weeks → days of curriculum, tracks daily/weekly/monthly progress) and **coach** (a linked practice-partner who can view a learner's progress and log weekly ratings / monthly test scores on their behalf). See `.claude/agents/` for the build plan this was sequenced from; `welcome.tsx`/`dashboard.tsx` are being replaced with the real product surface, not final design.

This is a git repository (initialized partway through the build, so early history starts from a baseline snapshot rather than from scratch).

## Stack

- **Backend**: PHP 8.3, Laravel 13, SQLite (dev), session auth via **Laravel Fortify** for login/registration/password-reset only — **email verification is a custom 6-digit OTP flow** (`App\Actions\IssueEmailOtp`, `VerifyEmailOtpController`, `routes/auth.php`), not Fortify's signed link. **2FA and WebAuthn passkeys have been removed** (they shipped in the starter kit but are not part of this product) — don't reintroduce them or assume they exist. **Laravel Wayfinder** for typed route/action generation.
- **Frontend**: **Inertia.js v3** + **React 19** + TypeScript, **Tailwind CSS v4** (CSS-based config, no `tailwind.config.js`), **shadcn/ui** (`new-york` style, Radix primitives, `lucide-react` icons).
- **Build/tooling**: Vite via `vite-plus` ("vp") — bundles dev server, linting, and formatting in one tool.
- **Testing**: **Pest 5** (`tests/Feature`, `tests/Unit`).
- **Quality gates**: **Pint** (`laravel` preset, PHP formatting), **Larastan/PHPStan** at **level 7** (`phpstan.neon`, scans `app/`, `bootstrap/app.php`, `config/`, `database/`, `routes/`).

## Commands

```bash
composer run dev          # serve + queue:listen + vite, concurrently (primary local dev loop)
composer run lint         # pint --parallel (auto-fix PHP style)
composer run lint:check   # pint --test (CI-style check, no writes)
composer run types:check  # phpstan analyse (level 7)
composer test             # config:clear -> lint:check -> types:check -> php artisan test
php artisan test          # Pest suite only
php artisan test --filter=Name

npm run dev                # vite dev server only
npm run build               # production build
npm run build:ssr           # production build incl. SSR bundle
npm run check                # vp check — ESLint-equivalent (type-aware, warnings fail: denyWarnings)
npm run check:fix           # vp check --fix
npm run types:check          # tsc --noEmit
```

Run `composer test` (or at minimum `lint:check` + `types:check` + the relevant Pest tests) before considering backend work done. Run `npm run check` and `npm run types:check` before considering frontend work done.

## Directory map

```
app/
  Actions/Fortify/        Fortify hooks (CreateNewUser — now also handles role + LearnerProgram creation)
  Actions/                 Non-Fortify actions: IssueEmailOtp, GenerateCoachInviteCode, RedeemCoachInviteCode
  Concerns/                Shared validation-rule traits (Password/Profile)
  Enums/                   PHP 8.3 backed enums: UserRole, SkillArea, MistakeTag, RoleplayDomain,
                            RoleplaySide, CoachLinkStatus — cast onto the model columns below
  Http/Controllers/        Keep thin; Settings/ subfolder for account-settings controllers;
                            Auth/ for VerifyEmailOtpController
  Http/Requests/Settings/  Form request validation
  Http/Middleware/         HandleInertiaRequests (shared Inertia props), HandleAppearance
  Models/                  Eloquent models (PHP attributes: #[Fillable], #[Hidden] — see User).
                            Curriculum: Month, Week, Day, DayTask, DayTaskVocabularyItem, MonthlyTest,
                            MonthlyTestSection. Content library: WordBankGroup, WordBankEntry,
                            CommonMistake, RoleplayScenario, RoleplayLine, AiPromptCategory, AiPrompt,
                            ConfidenceTopic, ConfidenceQuestion. Per-learner progress: LearnerProgram,
                            LearnerDayTask, LearnerWeeklyMilestone, LearnerWeeklyRating,
                            LearnerMonthlyTestRecord, LearnerMonthlyTestRecordScore. Coach: CoachLink.
                            Auth: EmailOtp.
  Notifications/           EmailOtpNotification (ShouldQueue)
  Policies/                CoachLinkPolicy, LearnerProgramPolicy, + stubs for the progress-engine
                            policies (WeeklyMilestonePolicy, WeeklyRatingPolicy,
                            MonthlyTestRecordPolicy, LearnerDayTaskPolicy) — registered in
                            AppServiceProvider::boot() via Gate::policy()
  Providers/                AppServiceProvider (policy registration, rate limiting — deliberately does
                            NOT wire the Registered event; see Conventions below for why),
                            FortifyServiceProvider (registration/reset-password views + rate limiters
                            only — no more 2FA/passkey views)

resources/
  js/pages/           Inertia page components (route targets, one per URL). auth/verify-email.tsx is
                       OTP entry, not a "click the link" screen. settings/coach.tsx (learner invite
                       management), coach/redeem.tsx, coach/learners/{index,show}.tsx are role-specific.
  js/layouts/         app/, auth/, settings/ layout shells
  js/components/      Shared React components; components/ui/ is shadcn-managed — regenerate
                       via shadcn CLI rather than hand-editing where possible
  js/hooks/           React hooks (use-appearance, etc.)
  js/actions/         GENERATED by Wayfinder — do not hand-edit, excluded from lint/format.
                       Regenerate with `php artisan wayfinder:generate --with-form` (the bare command
                       drops .form() helpers even though vite.config.ts's formVariants:true implies
                       they should exist — always pass --with-form).
  js/wayfinder/       GENERATED by Wayfinder — do not hand-edit
  js/types/           Shared TS types, re-exported from types/index.ts
  css/app.css          Tailwind v4 config lives here (@theme, CSS variables), not a JS config file
  views/app.blade.php  Inertia root template

routes/
  web.php        Public + authenticated app routes
  auth.php       OTP verification routes (verification.notice/store/send) — required from web.php
  coach.php      Coach-role routes (/coach/learners, /coach/redeem) — required from web.php
  settings.php   /settings/* routes (profile, security [password-only now], appearance, coach invite)
  console.php    Artisan console routes

database/
  migrations/    Curriculum, content-library, per-learner-progress, and coach_links tables layered on
                  top of the starter kit's users/cache/jobs tables. 2FA/passkey columns and the
                  passkeys table were dropped via an additive migration (the original starter-kit
                  migrations that created them were left untouched — once migrations are in git,
                  append a reversing migration rather than editing/deleting history).
  factories/     One factory per model above, for tests/dev seeding — not real lesson content.
  seeders/       DatabaseSeeder + per-content-type seeder stubs (MonthSeeder, WordBankSeeder,
                  CommonMistakeSeeder, RoleplaySeeder, AiPromptSeeder, ConfidenceQaSeeder,
                  MonthlyTestSeeder) — real content is authored by the english-tutor agent, not
                  invented by whoever builds the schema.

tests/
  Feature/       Pest feature tests (Auth/, Settings/, Dashboard, and growing). RefreshDatabase is
                  now enabled for the Feature suite (was commented out in the stock starter kit —
                  that was a real bug, not a deliberate setting; don't re-disable it).
  Unit/
  Pest.php       Test config
```

## Conventions and gotchas

- **Wayfinder-generated code** (`resources/js/actions/**`, `resources/js/wayfinder/**`, and any `resources/js/routes/**`) is regenerated from PHP routes/controllers on build — never hand-edit it, and don't "fix" lint issues in it (it's already excluded from `vp check`/`vp fmt`).
- **`components/ui/*`** is shadcn/ui-managed and excluded from lint/format for the same reason. Add new primitives with the shadcn CLI (config in `components.json`, aliases `@/components`, `@/lib`, `@/ui`, `@/hooks`) rather than writing them by hand.
- **Tailwind v4** has no `tailwind.config.js`; tokens/theme live in `resources/css/app.css` via `@theme`/CSS variables. Class sorting is enforced by `vp fmt` (`sortTailwindcss`, entry point `resources/css/app.css`).
- **PHP models use attributes**, not `$fillable`/`$hidden` arrays — see `App\Models\User` (`#[Fillable]`, `#[Hidden]`). Follow that pattern for new models.
- **Shared Inertia props** are defined once in `app/Http/Middleware/HandleInertiaRequests.php` (`name`, `auth.user`, `sidebarOpen`). Add new global props there rather than passing them from every controller.
- **Auth is a hybrid**: login/registration/password-reset stay Fortify-driven — customize via `FortifyServiceProvider` and `Actions/Fortify/*`, don't hand-roll those. **Email verification is not** — it's a custom OTP flow (`App\Actions\IssueEmailOtp`, `App\Notifications\EmailOtpNotification`, `VerifyEmailOtpController`), wired in only via the framework-level `MustVerifyEmail` contract on `User`. There is no 2FA and no passkeys; don't add UI or backend branches assuming either exists.
- **Do not add `Event::listen(Registered::class, SendEmailVerificationNotification::class)` anywhere.** Laravel 11+ already registers this automatically and unconditionally on every boot (`Illuminate\Foundation\Application` directly instantiates the framework's base `EventServiceProvider`, whose `configureEmailVerification()` wires this default unless an app-level `$listen[Registered::class]` override exists — see `vendor/laravel/framework/src/Illuminate/Foundation/Support/Providers/EventServiceProvider.php`). An earlier version of this app _did_ add that line explicitly, assuming it was needed — it wasn't, and the result was two `IssueEmailOtp` calls per registration (two different codes emailed, only the second valid), a real bug a live browser test caught that the Pest suite didn't (the existing OTP tests call `IssueEmailOtp` directly, bypassing the `Registered` event entirely). `tests/Feature/Auth/RegistrationTest.php`'s `"registering issues exactly one email OTP..."` test guards against this regressing — if you find yourself wanting to touch event wiring for `Registered`, read that test first.
- **Curriculum/content-library data is global and seeder-authored** (Month/Week/Day/DayTask, WordBank*, CommonMistake, Roleplay*, AiPrompt*, Confidence*, MonthlyTest*) — identical for every learner, never scoped to a user. **Per-learner state lives under `LearnerProgram`** (`learner_day_tasks`/`learner_weekly_milestones`/`learner_weekly_ratings`/`learner_monthly_test_records`), keyed by `learner_program_id` rather than `user_id` directly. A coach acting on a learner's behalf is authorized via `LearnerProgram::isManagedBy(User $user)`, not a direct ownership check — use the Policies in `app/Policies/`, don't hand-roll authorization checks in controllers.
- PHPStan runs at **level 7** — keep new code typed accordingly (property types, return types, generics via `@use`/`@property` PHPDoc where Eloquent needs it, as in `User`).
- `laravel/chisel` and `laravel/pao` are present as first-party Laravel dev dependencies beyond the standard starter kit; check `vendor/laravel/chisel` / `vendor/laravel/pao` source directly before relying on their behavior — don't assume based on the package name.

## Subagents

Role-specific subagents live in [.claude/agents/](.claude/agents/) and are invoked automatically by Claude Code when a request matches their description, or explicitly via the Agent tool / `@agent-name`. Roster:

| Agent               | Use for                                                                                                   |
| ------------------- | --------------------------------------------------------------------------------------------------------- |
| `project-manager`   | Breaking a feature/request into scoped tasks, sequencing work, tracking what's done vs. outstanding       |
| `project-architect` | System/DB/module design, new-feature architecture, cross-cutting technical decisions                      |
| `qa-lead`           | Test strategy, coverage review, release/quality gate sign-off                                             |
| `tester`            | Writing and maintaining Pest tests, chasing down failing tests                                            |
| `frontend`          | Inertia/React pages, components, hooks, client-side state                                                 |
| `backend`           | Laravel controllers, models, migrations, actions, routes, Fortify config                                  |
| `ui-ux`             | Visual/interaction design, shadcn/Tailwind design-system consistency, accessibility                       |
| `security`          | OTP/session hardening, coach-authorization boundary review, input validation, dependency and OWASP review |
| `english-tutor`     | Authoring pedagogically-sound English-learning content (vocab, grammar, exercises) for seeders/factories  |

See each agent file for its full scope and constraints.
