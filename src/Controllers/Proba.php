<?php

namespace Rubix\Server\Controllers;

use Rubix\ML\Estimator;
use Rubix\ML\Probabilistic;
use Rubix\ML\Wrapper;
use Rubix\ML\Datasets\Unlabeled;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use React\Http\Response as ReactResponse;
use RuntimeException;
use Exception;

class Proba extends Controller
{
    /**
     * The probabilistic estimator instance.
     * 
     * @var \Rubix\ML\Estimator
     */
    protected $estimator;

    /**
     * @param  \Rubix\ML\Estimator $estimator
     * @return void
     */
    public function __construct(Estimator $estimator)
    {
        $this->estimator = $estimator;
    }

    /**
     * Handle the request.
     * 
     * @param  Request  $request
     * @param  array  $params
     * @return Response
     */
    public function handle(Request $request, array $params) : Response
    {
        $json = json_decode($request->getBody()->getContents(), true);

        if (!isset($json['sample'])) {
            return new ReactResponse(400, self::DEFAULT_HEADERS, [
                'error' => 'Missing sample field in request body.',
            ]);
        }

        $dataset = Unlabeled::build($json['sample']);

        try {
            if ($this->estimator instanceof Probabilistic) {
                $probabilities = $this->estimator->proba($dataset);
            } else {
                throw new RuntimeException('A probabilistic'
                    . ' estimator is needed to handle this'
                    . ' request.');
            }
        } catch (Exception $e) {
            return new ReactResponse(500, self::DEFAULT_HEADERS, [
                'error' => $e->getMessage(),
            ]);
        }

        return new ReactResponse(200, self::DEFAULT_HEADERS, json_encode([
            'probabilities' => $probabilities[0],
        ]));
    }
}