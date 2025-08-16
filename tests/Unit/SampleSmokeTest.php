<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class SampleSmokeTest extends TestCase {
    public function testPhpWorks(): void {
        $this->assertTrue(function_exists('json_encode'));
    }
}
