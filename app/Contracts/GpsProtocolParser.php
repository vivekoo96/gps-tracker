<?php

namespace App\Contracts;

use App\Models\Device;

interface GpsProtocolParser
{
    /**
     * Check if this parser can handle the given data
     */
    public function canParse(string $data): bool;

    /**
     * Parse the raw data and extract GPS information
     * 
     * @return array [
     *   'type' => 'login'|'location'|'heartbeat'|'alarm',
     *   'imei' => string,
     *   'latitude' => float|null,
     *   'longitude' => float|null,
     *   'speed' => float|null,
     *   'course' => float|null,
     *   'altitude' => float|null,
     *   'satellites' => int|null,
     *   'timestamp' => string|null,
     *   'battery' => float|null,
     *   'signal' => int|null,
     *   'raw' => array (protocol-specific data)
     * ]
     */
    public function parse(string $data): array;

    /**
     * Build response for login packet
     */
    public function buildLoginResponse(Device $device): string;

    /**
     * Build response for heartbeat packet
     */
    public function buildHeartbeatResponse(): string;

    /**
     * Build response for location packet
     */
    public function buildLocationResponse(): string;

    /**
     * Get the protocol name
     */
    public function getProtocolName(): string;
}
