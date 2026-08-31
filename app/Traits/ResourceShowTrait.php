<?php

namespace App\Traits;

use App\Services\WpService;
use Illuminate\Http\JsonResponse;

trait ResourceShowTrait
{
    public function show(): JsonResponse
    {

        $singleResourceId = request()->route("resourceId");
        $bucket = WpService::bucket(request()->header('App-Version'));
        $cacheKey = "{$this->resourceSingular}:{$singleResourceId}:{$bucket}";

        if ($this->cacheService->exists($cacheKey)) {
            return response()->json(
                $this->cacheService->get($cacheKey),
            );
        }

        $resource = $this->wpService->get("{$this->resourceSingular}/{$singleResourceId}");

        if (empty($resource)) {
            return response()->json('No data available');
        }

        $this->cacheService->set($cacheKey, $resource);

        return response()->json($this->cacheService->get($cacheKey));

    }
}
