<?php

namespace Surveyforge\Surveyforge\Request;

final class RedirectContext
{
    private string $action;

    private ?string $reason;

    private ?string $reasonDetail;

    private string $serverId;

    private string $surveyId;

    public function __construct(
        string $action,
        ?string $reason,
        ?string $reasonDetail,
        string $serverId,
        string $surveyId
    ) {
        $this->action = $action;
        $this->reason = $reason;
        $this->reasonDetail = $reasonDetail;
        $this->serverId = $serverId;
        $this->surveyId = $surveyId;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function reasonDetail(): ?string
    {
        return $this->reasonDetail;
    }

    public function serverId(): string
    {
        return $this->serverId;
    }

    public function surveyId(): string
    {
        return $this->surveyId;
    }

    public function isSecurityRelated(): bool
    {
        return in_array($this->reason, [
            'activity_inactivity',
            'proctoring_violation',
            'proctoring_upload_failure',
            'proctoring_other',
        ], true);
    }

    public function isTimeout(): bool
    {
        return $this->action === 'complete' && $this->reason === 'timeout';
    }
}
