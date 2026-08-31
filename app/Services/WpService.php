<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;

class WpService
{
    /**
     * WP serves one data set below app version 3 and another from 3 up, and branches
     * on the version alone, so one header per bucket covers every platform.
     * Sent when warming the cache from the console, where no request exists.
     */
    public const BUCKET_HEADERS = [
        'legacy' => '2.0.0 android',
        'current' => '3.0.0 android',
    ];

    private array $lists = ['pois', 'routes', 'objects'];
    private array $categories = ['front-poi-categories', 'route-categories', 'poi-categories'];
    private array $pages = ['about', 'text-pages', 'contacts', 'misc-info', 'last-modified'];
    private array $fetchedLists = [];
    private array $fetchedCategories = [];
    private array $fetchedPages = [];
    private array $singleRecordsIDs = [];


    public function __construct(private ?string $bucket = null)
    {
    }

    public static function bucket(?string $appVersion): string
    {
        return (int)$appVersion >= 3 ? 'current' : 'legacy';
    }

    private function appVersionHeader(): ?string
    {
        if ($this->bucket) {
            return self::BUCKET_HEADERS[$this->bucket];
        }

        // A request without the header is cached under 'legacy', so ask WP for that
        // same set rather than letting it pick a default that contradicts the key.
        return request()->header('App-Version') ?: self::BUCKET_HEADERS[self::bucket(null)];
    }


    public function getSingleRecordsIDs(): array
    {
        if (empty($this->fetchedLists)) {
            $this->getLists('v2');
            $this->getLists('v3');

        }

        foreach ($this->fetchedLists as $list => $data) {

            if(!$decodedData = json_decode($data)) {
                continue;
            }

            $listTitleSingular = Str::singular($list);

            $this->singleRecordsIDs[$listTitleSingular] = array_map(function ($item) use ($listTitleSingular) {

                $itemArr = array_values((array)$item);
                return $itemArr[0] ?? null;

            }, $decodedData);

        }

        return $this->singleRecordsIDs;

    }

    public function getLists($version): array
    {
        if (empty($this->lists)) {
            return [];
        }

        foreach ($this->lists as $list) {

            $fetchedListData = $this->get($list, $version);
            $this->fetchedLists[$list] = $fetchedListData;

        }

        return $this->fetchedLists;

    }

    public function get(string $resource, string $version = null): string
    {
        if($version) {
            $url = config('api.api_url') . $version . "/" . $resource;
        } else {
            $url = config('api.api_url') . $resource;
        }

        $request = Http::acceptJson()->timeout(60);

        if ($appVersion = $this->appVersionHeader()) {
            $request = $request->withHeaders(['App-Version' => $appVersion]);
        }

        $responseData = $request->get($url)->json();

        if (!$this->isValid($responseData)) {
            return '';
        }

        return json_encode($responseData);
    }

    private function isValid($data): bool
    {

        if (!is_array($data)) {
            return false;
        }

        if (empty($data)) {
            return false;
        }

        if (!empty($data['code'])) {
            return false;
        }

        return true;
    }

    public function getCategories($version): array
    {
        if (empty($this->categories)) {
            return [];
        }

        foreach ($this->categories as $category) {

            $fetchedCategoryData = $this->get($category, $version);
            $this->fetchedCategories[$category] = $fetchedCategoryData;

        }

        return $this->fetchedCategories;
    }

    public function getPages($version): array
    {
        if (empty($this->pages)) {
            return [];
        }

        foreach ($this->pages as $page) {

            $fetchedPageData = $this->get($page, $version);
            $this->fetchedPages[$page] = $fetchedPageData;

        }

        return $this->fetchedPages;
    }

}
