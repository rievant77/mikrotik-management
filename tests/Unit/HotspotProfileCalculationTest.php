<?php

namespace Tests\Unit;

use App\Support\FormatHelper;
use PHPUnit\Framework\TestCase;

class HotspotProfileCalculationTest extends TestCase
{
    public function test_calculate_fup_rate_limit_with_symmetric_mbps(): void
    {
        $result = FormatHelper::calculateFupRateLimit('10M/10M', 50);
        $this->assertEquals('5M/5M', $result);
    }

    public function test_calculate_fup_rate_limit_with_asymmetric_mixed_units(): void
    {
        $result = FormatHelper::calculateFupRateLimit('512k/2M', 50);
        $this->assertEquals('256k/1M', $result);
    }

    public function test_calculate_fup_rate_limit_single_stream_and_edge_values(): void
    {
        $this->assertEquals('500k', FormatHelper::calculateFupRateLimit('1M', 50));
        $this->assertEquals('10M/10M', FormatHelper::calculateFupRateLimit('10M/10M', 100));
        $this->assertNull(FormatHelper::calculateFupRateLimit(null, 50));
        $this->assertNull(FormatHelper::calculateFupRateLimit('', 50));
        $this->assertNull(FormatHelper::calculateFupRateLimit('10M/10M', 0));
    }

    public function test_calculate_fup_rate_limit_with_raw_numeric_bps_and_minimum_clamping(): void
    {
        // 1000000 bps = 1000 kbps = 1M, at 50% = 500 kbps -> '500k'
        $this->assertEquals('500k/500k', FormatHelper::calculateFupRateLimit('1000000/1000000', 50));

        // Sub-1k clamping: 1k at 1% = 0.01 kbps -> minimum clamped to '1k'
        $this->assertEquals('1k', FormatHelper::calculateFupRateLimit('1k', 1));
    }

    public function test_calculate_cost_price_by_percentage(): void
    {
        $cost = FormatHelper::calculateCostPrice('percentage', 30, 10000, 0);
        $this->assertEquals(3000.0, $cost);
    }

    public function test_calculate_cost_price_by_fixed_and_custom(): void
    {
        $fixed = FormatHelper::calculateCostPrice('fixed', null, 10000, 2500);
        $this->assertEquals(2500.0, $fixed);

        $custom = FormatHelper::calculateCostPrice('custom', null, 10000, 4200);
        $this->assertEquals(4200.0, $custom);
    }
}
