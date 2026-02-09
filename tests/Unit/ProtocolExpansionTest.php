<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ProtocolDetector;
use App\Services\Protocols\TeltonikaParser;
use App\Services\Protocols\QueclinkParser;
use App\Services\Protocols\GT06Parser;

class ProtocolExpansionTest extends TestCase
{
    protected $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ProtocolDetector();
    }

    public function test_detects_gt06()
    {
        $hex = '787805010001D9DC0D0A'; // Mock GT06 Login
        $data = hex2bin($hex);
        $parser = $this->detector->detect($data);
        $this->assertInstanceOf(GT06Parser::class, $parser);
    }

    public function test_detects_teltonika()
    {
        $hex = '000000000000000F08010000016BBAA3030001000000000000000001'; // Mock Teltonika
        $data = hex2bin($hex);
        $parser = $this->detector->detect($data);
        $this->assertInstanceOf(TeltonikaParser::class, $parser);
    }

    public function test_detects_queclink()
    {
        $data = '+RESP:GTFRI,020101,123456789012345,VNAME,1234,0,1,0,50,180,100,78.12345,17.12345,20240101120000,404,01,1234,5678,0,01,ABCD$';
        $parser = $this->detector->detect($data);
        $this->assertInstanceOf(QueclinkParser::class, $parser);
    }
}
