<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Docsmith\Docsmith;

$siteUrl = getenv('DOCS_SITE_URL') ?: 'https://mrpunyapal.github.io/filament-infinite-select';
$editBranch = getenv('DOCS_EDIT_BRANCH') ?: 'main';
$baseUrl = getenv('DOCS_BASE_URL') ?: '/filament-infinite-select/';

Docsmith::make()
    ->source(__DIR__.'/md')
    ->output(__DIR__.'/docs')
    ->title('Filament Infinite Select')
    ->description('A Filament Select component with infinite scroll lazy loading for large option datasets.')
    ->repositoryUrl('https://github.com/mrpunyapal/filament-infinite-select')
    ->siteUrl($siteUrl)
    ->editBranch($editBranch)
    ->editPrefix('md')
    ->baseUrl($baseUrl)
    ->accentColor('#f59e0b')
    ->accentColorDark('#fbbf24')
    ->rightSidebar()
    ->build();
