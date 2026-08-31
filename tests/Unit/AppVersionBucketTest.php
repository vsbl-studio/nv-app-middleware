<?php

namespace Tests\Unit;

use App\Services\WpService;
use PHPUnit\Framework\TestCase;

class AppVersionBucketTest extends TestCase
{
    public function test_bucket_splits_at_version_three(): void
    {
        $this->assertSame('legacy', WpService::bucket('2.4.0 android'));
        $this->assertSame('legacy', WpService::bucket('2.99.9 ios'));
        $this->assertSame('current', WpService::bucket('3.0.2 android'));
        $this->assertSame('current', WpService::bucket('4.1.0 ios'));

        // No header means a client older than the header itself.
        $this->assertSame('legacy', WpService::bucket(null));
        $this->assertSame('legacy', WpService::bucket(''));

        // Every bucket the console warms must be reachable from a real header.
        foreach (WpService::BUCKET_HEADERS as $bucket => $header) {
            $this->assertSame($bucket, WpService::bucket($header));
        }
    }
}
