<?php

namespace Rubix\Server\Handlers;

use Rubix\ML\Ranking;
use Rubix\Server\Commands\Score;
use Rubix\Server\Responses\ScoreResponse;

class ScoreHandler
{
    /**
     * The ranking model that is being served.
     *
     * @var \Rubix\ML\Ranking
     */
    protected $estimator;

    /**
     * @param \Rubix\ML\Ranking $estimator
     */
    public function __construct(Ranking $estimator)
    {
        $this->estimator = $estimator;
    }

    /**
     * Handle the command.
     *
     * @param \Rubix\Server\Commands\Score $command
     * @return \Rubix\Server\Responses\ScoreResponse
     */
    public function __invoke(Score $command) : ScoreResponse
    {
        $scores = $this->estimator->score($command->dataset());

        return new ScoreResponse($scores);
    }
}