<?php

declare(strict_types=1);

namespace Nazmul\Socks\Requests;

abstract class Request
{
    protected int $version;

    protected int $position = 0;

    protected int $length;

    public function __construct(protected mixed &$data)
    {
        $this->length = strlen($data);
        $this->version = $this->nextByte(true);

        if($this->version !== 5){
            throw new \Exception("Only socks 5 is supported");
        }

        $this->process();
    }

    /**
     * Process the request
     *
     * @return void
     */
    abstract protected function process();

    /**
     * Read one byte from the request data
     *
     * @param boolean $format
     * @return int|string
     */
    protected function nextByte($format = true)
    {
        $byte = $this->data[$this->position++];
        if(!$format){
            return $byte;
        }
        return ord($byte);
    }


    /**
     * Read next n bytes in the given format
     *
     * @param string $format
     * @param integer $bytes
     * @return array
     */
    protected function nextBytes($format = 'C*', $bytes = 1)
    {
        $response = unpack($format, substr($this->data, $this->position, $bytes));
        $this->position += $bytes;

        return $response;
    }
}