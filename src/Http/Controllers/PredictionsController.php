<?php

namespace Rubix\Server\Http\Controllers;

use Rubix\Server\Commands\Predict;
use Rubix\Server\Payloads\ErrorPayload;
use Rubix\Server\Http\Responses\UnprocessableEntity;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Rubix\Server\Helpers\JSON;
use Exception;

class PredictionsController extends RESTController
{
    /**
     * Handle the request and return a response or a deferred response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface|\React\Promise\PromiseInterface
     */
    public function __invoke(Request $request)
    {
        try {
            $json = (array) $request->getParsedBody();

            $command = Predict::fromArray($json);
        } catch (Exception $exception) {
            $payload = ErrorPayload::fromException($exception);

            $data = JSON::encode($payload->asArray());

            return new UnprocessableEntity(self::HEADERS, $data);
        }

        return $this->bus->dispatch($command)->then(
            [$this, 'respondSuccess'],
            [$this, 'respondServerError']
        );
    }
}
