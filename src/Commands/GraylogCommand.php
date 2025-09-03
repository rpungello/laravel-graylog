<?php

namespace Rpungello\Graylog\Commands;

use Illuminate\Console\Command;

class GraylogCommand extends Command
{
    public $signature = 'laravel-graylog';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
