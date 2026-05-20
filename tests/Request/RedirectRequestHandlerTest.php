<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;
use Surveyforge\Surveyforge\Events\SurveyForgeCompleteEvent;
use Surveyforge\Surveyforge\Events\SurveyForgePauseEvent;
use Surveyforge\Surveyforge\Request\RedirectContext;
use Surveyforge\Surveyforge\Request\RedirectRequestHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Stub that bypasses real signature verification and network survey lookup.
 * The dispatch / context / validator logic is what these tests target.
 */
class StubbedRedirectRequestHandler extends RedirectRequestHandler
{
    protected function handleSecurityValidation()
    {
        // bypass — assume signature is good for these tests
        return null;
    }

    protected function getDeployedSurvey(): DeployedSurvey
    {
        return new DeployedSurvey($this->request->get('survey_id'));
    }
}

/**
 * Variant that simulates a failed signature verification, so we can prove
 * handle() actually short-circuits when onSecurityValidationFailed returns
 * a response.
 */
class FailingSecurityRedirectRequestHandler extends StubbedRedirectRequestHandler
{
    public bool $pauseRan = false;

    protected function handleSecurityValidation()
    {
        // Mirror the production failure path: invoke the configured callback
        // and return its response so handle() can bubble it up. If no callback
        // is configured, abort(401) like production does.
        $callable = $this->onSecurityFailed;
        if ($callable && is_callable($callable)) {
            return call_user_func($callable);
        }
        if ($callable) {
            return \Illuminate\Support\Facades\Redirect::to($callable);
        }
        abort(401);
    }
}

beforeEach(function () {
    // Avoid the trait's setApiFromConfig() reading null config.
    config()->set('surveyforge.servers.default', [
        'id' => 'test-server',
        'url' => 'https://example.test',
        'token' => 'test-token',
    ]);
});

function makeRedirectRequest(array $query): Request
{
    return Request::create('https://example.test/surveyforge/redirect', 'GET', array_merge([
        'survey_id' => '11111111-1111-1111-1111-111111111111',
        'server_id' => 'test-server',
        'signature' => 'sig',
    ], $query));
}

it('passes both survey and context to a 2-arg pause callback when reason is present', function () {
    $captured = null;

    $request = makeRedirectRequest([
        'action' => 'pause',
        'reason' => 'proctoring_violation',
        'reason_detail' => 'face_not_visible',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onPause(function (DeployedSurvey $survey, RedirectContext $context) use (&$captured) {
            $captured = ['survey' => $survey, 'context' => $context];
        })
        ->handle();

    expect($captured)->not->toBeNull();
    expect($captured['survey'])->toBeInstanceOf(DeployedSurvey::class);
    expect($captured['context'])->toBeInstanceOf(RedirectContext::class);
    expect($captured['context']->action())->toBe('pause');
    expect($captured['context']->reason())->toBe('proctoring_violation');
    expect($captured['context']->reasonDetail())->toBe('face_not_visible');
    expect($captured['context']->isSecurityRelated())->toBeTrue();
});

it('reports manual pause as not security-related', function () {
    $captured = null;

    $request = makeRedirectRequest([
        'action' => 'pause',
        'reason' => 'manual',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onPause(function (DeployedSurvey $survey, RedirectContext $context) use (&$captured) {
            $captured = $context;
        })
        ->handle();

    expect($captured->reason())->toBe('manual');
    expect($captured->isSecurityRelated())->toBeFalse();
});

it('flags timeout completes via isTimeout()', function () {
    $captured = null;

    $request = makeRedirectRequest([
        'action' => 'complete',
        'reason' => 'timeout',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onComplete(function (DeployedSurvey $survey, RedirectContext $context) use (&$captured) {
            $captured = $context;
        })
        ->handle();

    expect($captured->isTimeout())->toBeTrue();
    expect($captured->isSecurityRelated())->toBeFalse();
});

it('accepts redirects without a reason (old server) and yields null reason', function () {
    $captured = null;

    $request = makeRedirectRequest([
        'action' => 'pause',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onPause(function (DeployedSurvey $survey, RedirectContext $context) use (&$captured) {
            $captured = $context;
        })
        ->handle();

    expect($captured->reason())->toBeNull();
    expect($captured->reasonDetail())->toBeNull();
    expect($captured->isSecurityRelated())->toBeFalse();
});

it('still invokes legacy 1-arg pause callbacks without raising', function () {
    $captured = null;

    $request = makeRedirectRequest([
        'action' => 'pause',
        'reason' => 'manual',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onPause(function (DeployedSurvey $survey) use (&$captured) {
            $captured = $survey;
        })
        ->handle();

    expect($captured)->toBeInstanceOf(DeployedSurvey::class);
});

it('aborts 400 when reason exceeds 64 characters', function () {
    $request = makeRedirectRequest([
        'action' => 'pause',
        'reason' => str_repeat('x', 65),
    ]);

    expect(fn () => (new StubbedRedirectRequestHandler($request))->handle())
        ->toThrow(HttpException::class);
});

it('dispatches SurveyForgePauseEvent with the redirect context populated', function () {
    Event::fake();

    $request = makeRedirectRequest([
        'action' => 'pause',
        'reason' => 'proctoring_violation',
        'reason_detail' => 'face_not_visible',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onPause(function (DeployedSurvey $survey, RedirectContext $context) {
            event(new SurveyForgePauseEvent($survey, $context));
        })
        ->handle();

    Event::assertDispatched(SurveyForgePauseEvent::class, function (SurveyForgePauseEvent $event) {
        return $event->context instanceof RedirectContext
            && $event->context->reason() === 'proctoring_violation'
            && $event->context->reasonDetail() === 'face_not_visible'
            && $event->context->isSecurityRelated() === true;
    });
});

it('short-circuits to the security-failed handler instead of dispatching pause/complete', function () {
    $pauseRan = false;
    $securityResponse = new \Illuminate\Http\Response('blocked', 401);

    $request = makeRedirectRequest([
        'action' => 'pause',
        'reason' => 'proctoring_violation',
    ]);

    $response = (new FailingSecurityRedirectRequestHandler($request))
        ->onSecurityValidationFailed(function () use ($securityResponse) {
            return $securityResponse;
        })
        ->onPause(function () use (&$pauseRan) {
            $pauseRan = true;
        })
        ->handle();

    expect($response)->toBe($securityResponse);
    expect($pauseRan)->toBeFalse();
});

it('dispatches SurveyForgeCompleteEvent with context for timeout', function () {
    Event::fake();

    $request = makeRedirectRequest([
        'action' => 'complete',
        'reason' => 'timeout',
    ]);

    (new StubbedRedirectRequestHandler($request))
        ->onComplete(function (DeployedSurvey $survey, RedirectContext $context) {
            event(new SurveyForgeCompleteEvent($survey, $context));
        })
        ->handle();

    Event::assertDispatched(SurveyForgeCompleteEvent::class, function (SurveyForgeCompleteEvent $event) {
        return $event->context instanceof RedirectContext
            && $event->context->isTimeout() === true;
    });
});
