<?php

declare(strict_types=1);

namespace Nazmul\Socks\Requests;

class AuthenticationRequest extends Request
{
    /**
     * Number of authentication methods supported
     *
     * @var integer
     */
    protected int $supported;

    /**
     * Holds the supported authentication methods
     *
     * @var int[]
     */
    protected array $methods;

    /**
     * Process the request data from the second byte
     * 
     *  +----+----------+----------+
     *  |VER | NMETHODS | METHODS  |
     *  +----+----------+----------+
     *  | 1  |    1     | 1 to 255 |
     *  +----+----------+----------+
     *
     * @return void
     */
    protected function process()
    {
        /**
         * For authentication requests, the second byte holds the number of <br> Authentication methods supported by the peer
         * The subsequent n bytes each represents a supported Authentication mechanism
         */
        $this->supported = $this->nextByte(true);
        if($this->supported + 2 !== $this->length){
            var_dump([
                $this->supported,
                $this->length,
                $this->data,
                unpack('C*', $this->data)
            ]);
            // Version byte, number of methods byte & n method byte should be equal to the total data length
            throw new \Exception("Request lenght mismatch");
        }
        $this->methods = $this->nextBytes('n*', $this->supported);
    }

    /**
     * Get the number of supported method count
     *
     * @return int
     */
    public function methodCount(): int
    {
        return $this->supported;
    }

    /**
     * Get the supported authentication methods
     *
     * @return int[]
     */
    public function methods(): array
    {
        return $this->methods;
    }
}
