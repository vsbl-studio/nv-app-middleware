<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\WpService;
use App\Traits\ResourceIndexTrait;
use App\Traits\ResourceShowTrait;
use Illuminate\Http\JsonResponse;

class TextPagesController extends Controller
{
    use ResourceIndexTrait;

    protected string $resource = 'text-pages';


    public function __construct(
        private CacheService $cacheService,
        private WpService    $wpService,
    )
    {
    }

}

