<?php

declare(strict_types=1);

namespace Nazmul\Socks;

trait HasEvents
{
    /**
     * @var Array<string, callable>
     */
    protected array $registry;
    
    /**
     * Register a callback for an event
     * @param string $event
     * @param callable $callback
     * @return static
     */
    public function on(string $event, callable $callback): static
    {
        if(!isset($this->registry[$event])){
            $this->registry[$event] = [];
        }
        $this->registry[$event][] = $callback;

        return $this;
    }

    public function fire(string $event, ...$data)
    {
        foreach ($this->registry[$event] ?? [] as $callback) {
            call_user_func($callback, ...$data);
        }
    }
}