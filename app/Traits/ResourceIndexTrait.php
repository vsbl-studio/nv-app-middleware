<?php

namespace App\Traits;

use App\Services\WpService;
use Illuminate\Http\JsonResponse;

trait ResourceIndexTrait
{
    public function index(): JsonResponse
    {
        $cacheKey = $this->resource . ':' . WpService::bucket(request()->header('App-Version'));

        if ($this->cacheService->exists($cacheKey)) {
            return response()->json(
                $this->cacheService->get($cacheKey),
            );
        }

        $resource = $this->wpService->get($this->resource);

        if ($resource) {
            $this->cacheService->set($cacheKey, $resource);
        }

        return response()->json(
            $this->cacheService->get($cacheKey),
        );
    }
}
