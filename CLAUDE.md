# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this package is

A Laravel SDK that lets a host application **define surveys in PHP**, **deploy them to a remote Surveyforge server** over a REST API, and **handle the redirect/webhook lifecycle** (pause, complete, expire) when respondents return. It is distributed on Packagist as `ianrothmann/surveyforge-laravel`. There is no local survey storage — Surveyforge holds the canonical survey and answers; this SDK is a builder + thin client.

Package wiring uses `spatie/laravel-package-tools` in `src/SurveyforgeServiceProvider.php`: it publishes `config/surveyforge.php` and registers `routes/surveyforge-routes.php` (which adds `GET /surveyforge/redirect` → `SurveyforgeRedirectController`).

## Commands

```bash
composer test            # vendor/bin/pest
composer test-coverage   # vendor/bin/pest --coverage
composer format          # vendor/bin/pint (Laravel Pint code style)
composer analyse         # vendor/bin/phpstan analyse (no config file currently shipped)
```

Run a single test: `vendor/bin/pest --filter="part of test name"` or `vendor/bin/pest tests/ExampleTest.php`.

Tests run under Orchestra Testbench (`tests/TestCase.php`). The active test in `tests/ExampleTest.php` (`can create a survey on surveyforge server`) hits a real Surveyforge server with a hardcoded URL/token — it is an integration test, not a unit test, and will fail without a reachable server. Most of the file's tests are commented out and serve as runnable examples.

## Big-picture architecture

The data flow is **Definition → Flow → State → Deployment**, and you usually need to read across several of these to understand a change.

### 1. Definition layer — `src/Definitions/`

Fluent builders that produce a nested array description of a survey. Every builder extends `AbstractBuilder` (`src/Definitions/Builders/AbstractBuilder.php`), which:
- requires subclasses to set `$definitionType` (one of the constants in `Definitions/Interfaces/DefinitionType.php`: SURVEY, SECTION, QUESTION, FIELD, ANSWER, CONTENT, CONDITION, THEME, TEXT, TEXT_BAG),
- implements `build()` that calls the abstract `toArray()` and stamps every node with a generated `definition_id` (UUID) plus the `definition_type`.

Composition hierarchy (top → bottom): `Survey` → `Section`s → `AbstractQuestion`s (Vertical/Horizontal/Step) → `AbstractAnswerLayout` (Single/Form) → `AbstractField`s (`TextInput`, `CheckboxGroup`, `RadioGroup`, `Dropdown`, `NumberRating`, `Likert`, `MultiSelect`, `CountryInput`, `PhoneInput`, `UrlInput`, `TextArea`, `OptionsField`).

Cross-cutting concepts:
- **Conditions** — `Definitions/Condition/Condition.php` is a query-builder-style API (`->where`, `->orWhere`, `->whereIn`, `->whereNull`, nested closures) that compiles itself to **Symfony Expression Language syntax** in `parseConditionSyntax()`. The trait `ConditionHandlerTrait` adds `showWhen($closure)` / `doNotShowWhen($closure)` to sections and questions. Column names default to `<questionId>.<questionId>` when no dot notation is given (see `Condition::inferFullColumnName`).
- **Content** — `HtmlContent`, `QuestionContent`, `GridContent` attached as instructions, orientation, ending, terms, or question text.
- **Text/Translations** — `TextTranslator` registers languages and produces `Text` objects with per-language strings. Builders accept either a plain string or a `Text` instance (`AbstractBuilder::renderText`). At `Survey::toArray()`, the translator's `texts` are split out: the survey definition carries language metadata + text references, and the localized strings live in a separate `text` array. Only `en` and `nl` are accepted as system languages (`TextTranslator::validateSystemLanguage`).
- **Predefined** — `Definitions/Predefined/` holds curated fields/forms/surveys, e.g. `Predefined/Surveys/DemoSurvey.php` is the canonical end-to-end example and the one the active integration test uses.

### 2. Flow layer — `src/Flow/SurveyFlowCreator.php`

Takes the nested array from `Survey::build()` and **flattens it** into a presentation-order flow the client/server can step through:
- Walks sections → instructions + questions and emits one flat `flow` array.
- Extracts every `condition` node (section-level, question-level, and option-level discovered via `extractConditions`/`ArrayUtils::updateNodeRecursive`) into a top-level `conditions` map keyed by a fresh UUID; the originating node keeps only the UUID reference. Multiple conditions on a path are combined via `combineConditions`.
- Builds an `answer_object` (a schema of expected answer keys/types, registered by each question via `registerAnswerObject`) and rewrites each field with an `answer_ref` like `<question_id>.<field_id>`.
- Validates references afterwards (`checkConditionReferences`, `checkAnswerReferences`) and surfaces problems in a `warnings` collection on the result instead of throwing.

Output shape from `get()`: `survey` (truncated), `sections`, `flow`, `conditions`, `answer_object`, `theme`, `text`, `options`, `warnings`.

### 3. State layer — `src/State/SurveyStateHandler.php`

Walks the flow, evaluates conditions against a mutable `answerObject`, and supports answer validation. Key behaviors:
- `next()` parses every condition (`parseConditions`) by feeding `condition['syntax']` and the answers for `condition['columns']` into `Expression/Expression.php` (a thin wrapper over Symfony ExpressionLanguage that also registers `in_array` and `is_null`). Dot notation in column names is rewritten to underscores in the syntax via `replaceDotNotationWithUnderscores` so ExpressionLanguage accepts them as identifiers.
- `getInvalidFlowItems()` runs Laravel's `Validator` over every collected field using each field's `validator` string.
- Construct it via `fromSurvey()`, `fromSurveyDefinitionObject()`, `fromSurveyFlowCreator()`, or `fromSurveyFlowObject()`.

### 4. Deployment layer — `src/Deployment/`

- `Api/SurveyforgeApi.php` — raw cURL client. Talks to `{server_url}/api/v1/...` with a bearer token, returns Laravel `Collection`s, throws on non-2xx or when the response body has an `error` key.
- `Traits/HandlesApiCalls.php` — pulls server URL/token from `config('surveyforge.servers.default')`, with `onServer($profile)` to switch profiles and `onConnection($url, $token)` to override entirely.
- `Traits/HandlesSurveyApiCalls.php` — `surveys` CRUD wrappers (`createSurvey`, `getSurvey`, `patchSurvey`, `querySurveys`, `deleteSurvey`).
- `DeployedSurvey.php` — the user-facing handle. Fluent setters (`setDefinition`, `setSurvey`, `setTags`, `expiresAfter`, `redirectTo`, `notifyWhenComplete`, `toBeDeletedAfter`, `linkToBot`, `disableRedirect`) accumulate into `$dirty`; `save()` POSTs to create or PATCHes to update; `find($id)` / `refresh()` re-hydrates from the server. **On create, if no `redirect_to` was set and redirect is not disabled**, `createRedirectUrl()` auto-fills it with `route(config('surveyforge.redirect_route'))` or `route('surveyforge.sdk.redirect')` so the server has somewhere to send respondents back.
- `SurveyforgeVerifier.php` — calls `POST /verify/url` to check whether an inbound URL has a valid server signature; results are cached for the URL's remaining lifetime (or 60s) using the `expires` query parameter, and cache is bypassed entirely in debug mode.

### 5. Inbound redirect handling — `src/Request/RedirectRequestHandler.php` + `src/Controllers/SurveyforgeRedirectController.php`

The Surveyforge server redirects respondents to `surveyforge.sdk.redirect` with `survey_id`, `server_id`, `signature`, and `action` (`pause` | `complete` | `expire`). `RedirectRequestHandler`:
1. Validates the query params.
2. Verifies the signature by calling `SurveyforgeVerifier::verifyCurrentUrl()` against the server identified by `server_id` (looked up via `config('surveyforge.servers')` — note: searched by `id` field, not array key).
3. Dispatches to `onPause` / `onComplete` / `onExpire` callbacks (each may be a URL string or a callable that receives the hydrated `DeployedSurvey`).

The default controller fires `SurveyForgePauseEvent` / `SurveyForgeCompleteEvent` (see `src/Events/`) so host apps can listen rather than override the controller. To override behavior, set `config('surveyforge.redirect_route')` to a route name in the host app and point that route at a custom controller that uses `RedirectRequestHandler::forRequest(...)`.

### 6. URL signing — `src/Url/SurveyforgeUrlSigner.php`

Independent helper for signing/verifying URLs with an MD5-of-`host::path::sorted-query::secret` scheme. Adds an `expires` timestamp (default +60 min). Used externally rather than by the redirect flow (which verifies via the server API instead).

## Configuration

`config/surveyforge.php`:
```php
return [
    'redirect_route' => null,                    // optional named route to override the default redirect handler
    'servers' => [
        'default' => [
            'id'    => env('SURVEYFORGE_SERVER_ID'),
            'url'   => env('SURVEYFORGE_SERVER_URL'),
            'token' => env('SURVEYFORGE_AUTH_TOKEN'),
        ],
    ],
];
```

Add more entries under `servers` to use `->onServer('profileName')` on `DeployedSurvey` / `SurveyforgeVerifier`. Server lookup in `RedirectRequestHandler::findServerConfig` matches on the `id` field, so each profile's `id` must match what the Surveyforge server sends in `server_id`.

## Conventions worth knowing before editing

- **Every builder must set `$definitionType`** before `build()` is called or `AbstractBuilder::build()` throws. New question/field/content classes need a constant from `DefinitionType`.
- **Question IDs and field IDs are user-facing** — they become the keys in `answer_object` and the column names referenced from `Condition`. The auto-generated `definition_id` UUIDs are internal node identifiers; do not use them in conditions.
- **Conditions reference answers by dot path** (`question_id.field_id`). A bare `dogs` in a `where()` is auto-expanded to `dogs.dogs` by `Condition::inferFullColumnName`. If you see condition warnings from `SurveyFlowCreator`, this is the first thing to check.
- **`Survey::build()` returns the nested form**; `SurveyFlowCreator` (or `SurveyStateHandler::fromSurvey`) gives the flat runtime form. Server-side deployment via `DeployedSurvey::setSurvey($survey)` sends the nested form — flattening happens on the consuming side.
- **PHP 7.4+ / Laravel 10+** are the floor (`composer.json`). Avoid PHP 8.1-only syntax unless raising the floor intentionally.
