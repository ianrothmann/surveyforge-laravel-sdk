# Changelog

All notable changes to `surveyforge-laravel` will be documented in this file.

## Unreleased

### Added
- `RedirectContext` DTO exposed to `onPause` / `onComplete` / `onExpire` callbacks as an optional second argument, carrying `action`, `reason`, `reasonDetail`, `serverId`, `surveyId` and the `isSecurityRelated()` / `isTimeout()` helpers.
- `SurveyForgePauseEvent` and `SurveyForgeCompleteEvent` now carry the redirect context on a public `?RedirectContext $context` property.
- `RedirectRequestHandler` validator accepts optional `reason` and `reason_detail` query params (max 64 chars each); aborts 400 if they exceed the cap.
- Reason vocabulary documented in `docs/09-redirects-and-events.md`.

### Fixed
- `RedirectRequestHandler::handlePause` / `handleComplete` / `handleExpire` now `return` their `Redirect::to(...)` responses for the URL-string branches. Previously these redirects were silently discarded.

### Compatibility
- Fully backwards compatible. Existing 1-arg callbacks (`function (DeployedSurvey $survey) { … }`) continue to work — the handler uses reflection to dispatch with the right arity. Existing event listeners that only read `$event->survey` continue to work. Old Surveyforge servers that omit `reason` are accepted; `RedirectContext->reason()` is `null`.
