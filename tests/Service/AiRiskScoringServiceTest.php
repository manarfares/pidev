<?php

namespace App\Tests\Service;

use App\Service\AiRiskScoringService;
use PHPUnit\Framework\TestCase;

class AiRiskScoringServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['AI_RISK_AUTO_SUSPEND'], $_SERVER['AI_RISK_AUTO_SUSPEND']);
        unset($_ENV['AI_RISK_AUTO_SUSPEND_THRESHOLD'], $_SERVER['AI_RISK_AUTO_SUSPEND_THRESHOLD']);
    }

    public function testAutoSuspendEnabledAcceptsTruthyValues(): void
    {
        $_ENV['AI_RISK_AUTO_SUSPEND'] = 'true';
        $service = new AiRiskScoringService();

        self::assertTrue($service->isAutoSuspendEnabled());
    }

    public function testAutoSuspendEnabledReturnsFalseByDefault(): void
    {
        $service = new AiRiskScoringService();

        self::assertFalse($service->isAutoSuspendEnabled());
    }

    public function testAutoSuspendThresholdUsesDefaultForOutOfRangeValue(): void
    {
        $_ENV['AI_RISK_AUTO_SUSPEND_THRESHOLD'] = '5';
        $service = new AiRiskScoringService();

        self::assertSame(0.92, $service->getAutoSuspendThreshold());
    }
}
