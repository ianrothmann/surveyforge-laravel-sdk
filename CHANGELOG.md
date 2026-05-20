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
- **Security:** `RedirectRequestHandler::handleSecurityValidation` had the same dropped-return defect in its URL-string branch, and `handle()` did not bubble up the failure response from the callable branch. Any host application that configured `onSecurityValidationFailed(...)` with a URL or with a callable returning a response would silently fall through to `handlePause` / `handleComplete` / `handleExpire` as if the signature had verified, bypassing the security check. `handleSecurityValidation` now returns the failure response (or `null` on success) and `handle()` short-circuits when a response is returned. The default no-callback path (`abort(401)`) was not affected because `abort` throws.

### Compatibility
- Fully backwards compatible. Existing 1-arg callbacks (`function (DeployedSurvey $survey) { … }`) continue to work — the handler uses reflection to dispatch with the right arity. Existing event listeners that only read `$event->survey` continue to work. Old Surveyforge servers that omit `reason` are accepted; `RedirectContext->reason()` is `null`.
