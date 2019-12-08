<?php

namespace Rubix\Server\Tests\Responses;

use Rubix\Server\Responses\Response;
use Rubix\Server\Responses\ProbaSampleResponse;
use PHPUnit\Framework\TestCase;

class ProbaSampleResponseTest extends TestCase
{
    protected const EXPECTED_PROBABILITIES = [
        'monster' => 0.4,
        'not monster' => 0.6,
    ];

    /**
     * @var \Rubix\Server\Responses\ProbaSampleResponse
     */
    protected $response;

    public function setUp() : void
    {
        $this->response = new ProbaSampleResponse(self::EXPECTED_PROBABILITIES);
    }

    public function test_build_response() : void
    {
        $this->assertInstanceOf(ProbaSampleResponse::class, $this->response);
        $this->assertInstanceOf(Response::class, $this->response);
    }

    public function test_as_array() : void
    {
        $expected = [
            'probabilities' => self::EXPECTED_PROBABILITIES,
        ];

        $payload = $this->response->asArray();

        $this->assertIsArray($payload);
        $this->assertEquals($expected, $payload);
    }
}
