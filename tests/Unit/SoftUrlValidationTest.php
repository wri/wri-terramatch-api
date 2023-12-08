<?php

namespace Tests\Unit;

use App\Validators\Extensions\SoftUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SoftUrlValidationTest extends TestCase
{
    use RefreshDatabase;

    public function testInvalidUrlsFail(): void
    {
        $invalidDomains = [
            'http://url-with-protocol-without-top-level-domain',
            'https://url-with-protocol-with-trailing-dot.',
            'https://url-with-protocol-with-consecutive..dots',
            'url-without-protocol-without-top-level-domain',
            'url-without-protocol-with-trailing-dot.',
            'url-without-protocol-with-consecutive..dots',
        ];
        foreach ($invalidDomains as $invalidDomain) {
            $this->assertFalse(SoftUrl::passes('website', $invalidDomain, null, null));
        }
    }

    public function testValidUrlsPass(): void
    {
        $validDomains = [
            'http://url-with-protocol-with-top-level-domain.test',
            'https://url-with-protocol-with.sub-domain.com',
            'url-without-protocol-with-top-level-domain.test',
            'url-without-protocol-with.sub-domain.com',
        ];
        foreach ($validDomains as $validDomain) {
            $this->assertTrue(SoftUrl::passes('website', $validDomain, null, null));
        }
    }
}
