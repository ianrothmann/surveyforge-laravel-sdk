<?php

namespace Surveyforge\Surveyforge\Commands;

use Illuminate\Console\Command;

class SurveyforgeCommand extends Command
{
    public $signature = 'surveyforge-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
