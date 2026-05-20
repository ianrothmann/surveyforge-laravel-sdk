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
        // bypass
    }

    protected function getDeployedSurvey(): DeployedSurvey
    {
        return new DeployedSurvey($this->request->get('survey_id'));
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
