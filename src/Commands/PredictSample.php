<?php

namespace Rubix\Server\Commands;

use InvalidArgumentException;

/**
 * Predict Sample
 *
 * Predict a single sample and return the result.
 *
 * @category    Machine Learning
 * @package     Rubix/Server
 * @author      Andrew DalPino
 */
class PredictSample extends Command
{
    /**
     * The sample to predict.
     *
     * @var mixed[]
     */
    protected $sample;

    /**
     * Build the command from an associative array of data.
     *
     * @param mixed[] $data
     * @return self
     */
    public static function fromArray(array $data) : self
    {
        return new self($data['sample'] ?? []);
    }

    /**
     * @param mixed[] $sample
     * @throws \InvalidArgumentException
     */
    public function __construct(array $sample)
    {
        if (empty($sample)) {
            throw new InvalidArgumentException('Sample cannot be empty.');
        }
        
        $this->sample = $sample;
    }

    /**
     * Return the sample to predict.
     *
     * @return mixed[]
     */
    public function sample() : array
    {
        return $this->sample;
    }

    /**
     * Return the message as an array.
     *
     * @return mixed[]
     */
    public function asArray() : array
    {
        return [
            'sample' => $this->sample,
        ];
    }
}