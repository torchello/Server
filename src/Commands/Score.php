<?php

namespace Rubix\Server\Commands;

use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Unlabeled;

/**
 * Score
 *
 * @category    Machine Learning
 * @package     Rubix/Server
 * @author      Andrew DalPino
 */
class Score extends Command
{
    /**
     * The dataset to predict.
     *
     * @var \Rubix\ML\Datasets\Dataset<array>
     */
    protected $dataset;

    /**
     * Build the command from an associative array of data.
     *
     * @param mixed[] $data
     * @return self
     */
    public static function fromArray(array $data) : self
    {
        return new self(new Unlabeled($data['samples'] ?? []));
    }

    /**
     * @param \Rubix\ML\Datasets\Dataset<array> $dataset
     */
    public function __construct(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    /**
     * Return the dataset to predict.
     *
     * @return \Rubix\ML\Datasets\Dataset<array>
     */
    public function dataset() : Dataset
    {
        return $this->dataset;
    }

    /**
     * Return the message as an array.
     *
     * @return mixed[]
     */
    public function asArray() : array
    {
        return [
            'samples' => $this->dataset->samples(),
        ];
    }
}