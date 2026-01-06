<?php

namespace MrPunyapal\FilamentSelectWithLazyLoading\Commands;

use Illuminate\Console\Command;

class FilamentSelectWithLazyLoadingCommand extends Command
{
    public $signature = 'filament-select-with-lazy-loading';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
