<?php

namespace Rubix\Server\Commands;

use Rubix\Server\Exceptions\ValidationException;

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
     * @var (string|int|float)[]
     */
    protected $sample;

    /**
     * Build the command from an associative array of data.
     *
     * @param mixed[] $data
     * @throws \Rubix\Server\Exceptions\ValidationException
     * @return self
     */
    public static function fromArray(array $data) : self
    {
        if (!isset($data['sample'])) {
            throw new ValidationException('Sample property must be present.');
        }

        return new self($data['sample']);
    }

    /**
     * @param (string|int|float)[] $sample
     * @throws \Rubix\Server\Exceptions\ValidationException
     */
    public function __construct(array $sample)
    {
        if (empty($sample)) {
            throw new ValidationException('Sample must not be empty.');
        }

        $this->sample = $sample;
    }

    /**
     * Return the sample to predict.
     *
     * @return (string|int|float)[]
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

    /**
     * Return the string representation of the object.
     *
     * @return string
     */
    public function __toString() : string
    {
        return 'Predict Sample';
    }
}
