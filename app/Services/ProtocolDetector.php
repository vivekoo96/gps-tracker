<?php

namespace App\Services;

use App\Contracts\GpsProtocolParser;
use App\Services\Protocols\GT06Parser;
use App\Services\Protocols\TK103Parser;
use App\Services\Protocols\TeltonikaParser;
use App\Services\Protocols\QueclinkParser;
use App\Services\Protocols\TextParser;
use Illuminate\Support\Facades\Log;

class ProtocolDetector
{
    protected array $parsers = [];
    protected array $parserInstances = [];

    public function __construct()
    {
        // Register parsers in order of priority
        $this->parsers = [
            GT06Parser::class,
            TeltonikaParser::class,
            QueclinkParser::class,
            TK103Parser::class,
            TextParser::class, // Fallback
        ];
    }

    /**
     * Detect protocol and return appropriate parser
     */
    public function detect(string $data): GpsProtocolParser
    {
        foreach ($this->parsers as $parserClass) {
            $parser = $this->getParserInstance($parserClass);
            
            if ($parser->canParse($data)) {
                Log::info("Protocol detected: {$parser->getProtocolName()}");
                return $parser;
            }
        }

        // Fallback to text parser
        Log::warning("No protocol detected, using text parser fallback");
        return $this->getParserInstance(TextParser::class);
    }

    /**
     * Get parser by protocol name
     */
    public function getParser(string $protocolName): GpsProtocolParser
    {
        $parserMap = [
            'auto' => null, // Will use detect()
            'gt06' => GT06Parser::class,
            'teltonika' => TeltonikaParser::class,
            'queclink' => QueclinkParser::class,
            'tk103' => TK103Parser::class,
            'text' => TextParser::class,
        ];

        if (!isset($parserMap[$protocolName])) {
            Log::warning("Unknown protocol: {$protocolName}, using text parser");
            return $this->getParserInstance(TextParser::class);
        }

        if ($protocolName === 'auto') {
            throw new \InvalidArgumentException('Cannot get parser for "auto" protocol, use detect() instead');
        }

        return $this->getParserInstance($parserMap[$protocolName]);
    }

    /**
     * Get or create parser instance (cached)
     */
    protected function getParserInstance(string $parserClass): GpsProtocolParser
    {
        if (!isset($this->parserInstances[$parserClass])) {
            $this->parserInstances[$parserClass] = new $parserClass();
        }

        return $this->parserInstances[$parserClass];
    }

    /**
     * Get all available protocols
     */
    public function getAvailableProtocols(): array
    {
        return [
            'auto' => 'Auto-detect',
            'gt06' => 'GT06 / Concox',
            'teltonika' => 'Teltonika (Codec 8/16)',
            'queclink' => 'Queclink GV Series',
            'tk103' => 'TK103 / Xexun',
            'text' => 'Text-based (Generic)',
        ];
    }
}
