<?php

namespace Tests\Feature;

use App\Models\HotspotUser;
use App\Services\VoucherBatchService;
use Tests\TestCase;

class VoucherBatchTest extends TestCase
{
    public function test_batch_voucher_generation(): void
    {
        $service = new VoucherBatchService();
        $result = $service->generate([
            'profile' => 'Paket-3Jam',
            'qty' => 10,
            'credentialMode' => 'same',
            'charSet' => 'numeric',
            'prefix' => 'TEST-',
            'length' => 6,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(10, $result['count']);
        $this->assertCount(10, $result['vouchers']);

        foreach ($result['vouchers'] as $v) {
            $this->assertStringStartsWith('TEST-', $v['username']);
            $this->assertEquals($v['username'], $v['password']);
        }
    }
}
