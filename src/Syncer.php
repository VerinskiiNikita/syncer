<?php

namespace Sotoro\Syncer;

use Illuminate\Support\Facades\Cache;

/**
 * @property \Sotoro\Syncer\Server $master
 * @property \Sotoro\Syncer\Server $current
 * @property array<\Sotoro\Syncer\Server> $slaves
 */
class Syncer
{
    private $master;
    private $slaves;
    private $current;

    public function __construct(Server $master, Server $current, array $slaves)
    {
        $this->master = $master;
        $this->current = $current;
        $this->slaves = $slaves;
    }

    public function isMaster()
    {
        return $this->master->getName() == $this->current->getName();
    }

    /**
     *
     * @param array<string,int> $array
     * @return bool
     */
    public function setBalances(array $array): bool
    {
        if (! $this->isMaster()) {
            return false;
        }

        foreach ($array as $key => $quantity) {
            $this->master->setBalance($key, $quantity);
        }

        return true;
    }

    /**
     * @param string $key
     * @return integer
     */
    public function getBalance(string $key): int
    {
        return $this->master->getBalance($key);
    }

    public function getReserved(string $key): int
    {
        return array_sum($this->slaves)$this->master->getReserved($key);
    }

    public function getAvailable(string $key)
    {
        $masterBalance = $this->master->getBalance($key);
        foreach ($this->slaves as $server) {
            $masterBalance -= $server->getReserved($key);
        }
        return $masterBalance;
    }

    /**
     * Reserve $key for order
     *
     * @param string $key
     * @param string $id
     * @param integer $quantity
     * @return bool
     */
    public function reserve(string $key, string $id, int $quantity = 1): bool
    {
        if ($this->getAvailable($key) < $quantity) {
            return false;
        }

        return $this->current->reserve($key, $id, $quantity);
    }

    public function dereserve(string $key, string $id, int $quantity = 1): bool
    {

    }
}
