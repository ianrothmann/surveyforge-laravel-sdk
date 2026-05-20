<?php

namespace Surveyforge\Surveyforge\Request;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Surveyforge\Surveyforge\Deployment\Api\SurveyforgeApi;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;
use Surveyforge\Surveyforge\Deployment\SurveyforgeVerifier;
use Surveyforge\Surveyforge\Deployment\Traits\HandlesApiCalls;

class RedirectRequestHandler
{
    use HandlesApiCalls;

    protected Request $request;

    protected $onPause;

    protected $onComplete;

    protected $onExpire;

    protected $onSecurityFailed;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function forRequest(Request $request)
    {
        return new static($request);
    }

    public function onPause($urlOrcallable)
    {
        $this->onPause = $urlOrcallable;

        return $this;
    }

    public function onComplete($urlOrcallable)
    {
        $this->onComplete = $urlOrcallable;

        return $this;
    }

    public function onExpire($urlOrcallable)
    {
        $this->onExpire = $urlOrcallable;

        return $this;
    }

    public function onSecurityValidationFailed($urlOrcallable)
    {
        $this->onSecurityFailed = $urlOrcallable;

        return $this;
    }

    public function handle()
    {
        $validator = Validator::make($this->request->all(), [
            'survey_id' => 'required|uuid',
            'server_id' => 'required|string',
            'signature' => 'required|string',
            'action' => 'required|in:pause,complete,expire',
            'reason' => 'nullable|string|max:64',
            'reason_detail' => 'nullable|string|max:64',
        ]);

        if ($validator->errors()->count() > 0) {
            abort(400);
        }

        $this->handleSecurityValidation();

        $context = new RedirectContext(
            $this->request->input('action'),
            $this->request->input('reason'),
            $this->request->input('reason_detail'),
            $this->request->input('server_id'),
            $this->request->input('survey_id')
        );

        if ($this->request->get('action') === 'pause') {
            return $this->handlePause($context);
        } elseif ($this->request->get('action') == 'complete') {
            return $this->handleComplete($context);
        } elseif ($this->request->get('action') == 'expire') {
            return $this->handleExpire($context);
        } else {
            throw new \Exception('Invalid request');
        }
    }

    protected function handlePause(RedirectContext $context)
    {
        if ($this->onPause && is_callable($this->onPause)) {
            return $this->callHandler($this->onPause, fn () => $this->getDeployedSurvey(), $context);
        } elseif ($this->onPause) {
            return Redirect::to($this->onPause);
        }
    }

    protected function handleComplete(RedirectContext $context)
    {
        if ($this->onComplete && is_callable($this->onComplete)) {
            return $this->callHandler($this->onComplete, fn () => $this->getDeployedSurvey(), $context);
        } elseif ($this->onComplete) {
            return Redirect::to($this->onComplete);
        }
    }

    protected function handleExpire(RedirectContext $context)
    {
        if ($this->onExpire && is_callable($this->onExpire)) {
            return $this->callHandler($this->onExpire, fn () => $this->getDeployedSurvey(), $context);
        } elseif ($this->onExpire) {
            return Redirect::to($this->onExpire);
        }
        $deployedSurvey = $this->getDeployedSurvey();
        $url = $deployedSurvey->getUrl();

        return Redirect::to($url);
    }

    /**
     * Invoke a user-supplied callback, adapting to its declared parameter count
     * so existing 0/1-arg callbacks keep working while new 2-arg callbacks
     * also receive a RedirectContext.
     *
     * $surveyResolver is a closure that returns the hydrated DeployedSurvey on
     * demand, so 0-arg callbacks don't trigger a network fetch they don't need.
     */
    protected function callHandler(callable $handler, callable $surveyResolver, RedirectContext $context)
    {
        if ($handler instanceof \Closure) {
            $ref = new \ReflectionFunction($handler);
        } elseif (is_array($handler)) {
            $ref = new \ReflectionMethod($handler[0], $handler[1]);
        } elseif (is_string($handler) && strpos($handler, '::') !== false) {
            $ref = new \ReflectionMethod($handler);
        } elseif (is_object($handler) && method_exists($handler, '__invoke')) {
            $ref = new \ReflectionMethod($handler, '__invoke');
        } else {
            $ref = new \ReflectionFunction(\Closure::fromCallable($handler));
        }

        $paramCount = $ref->getNumberOfParameters();
        if ($paramCount >= 2) {
            return $handler($surveyResolver(), $context);
        }
        if ($paramCount === 1) {
            return $handler($surveyResolver());
        }

        return $handler();
    }

    protected function handleSecurityValidation()
    {
        if (! $this->api) {
            $serverConfig = $this->findServerConfig($this->request->get('server_id'));
            $this->api = new SurveyforgeApi($serverConfig['url'], $serverConfig['token']);
        }

        $success = $this->makeVerifier()->verifyCurrentUrl();

        if (! $success) {
            if ($this->onSecurityFailed && is_callable($this->onSecurityFailed)) {
                return call_user_func($this->onSecurityFailed);
            } elseif ($this->onSecurityFailed) {
                Redirect::to($this->onSecurityFailed);
            } else {
                abort(401);
            }
        }
    }

    protected function makeVerifier(): SurveyforgeVerifier
    {
        return (new SurveyforgeVerifier())->withApi($this->api);
    }

    protected function findServerConfig($url)
    {
        $serverId = $this->request->get('server_id');
        $server = collect(config('surveyforge.servers'))->where('id', $serverId)->first();
        if (! $server) {
            throw new \Exception('SurveyForge server ID not found in config: '.$serverId);
        }

        return $server;
    }

    protected function getDeployedSurvey(): DeployedSurvey
    {
        $deployedSurvey = DeployedSurvey::find($this->request->get('survey_id'));
        if (! $deployedSurvey) {
            throw new \Exception('Invalid survey id');
        }

        return $deployedSurvey;
    }
}
