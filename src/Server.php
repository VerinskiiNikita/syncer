<?php

namespace Sotoro\Syncer;

use Illuminate\Support\Facades\Cache;

class Server
{
    protected $name;
    protected $balance;
    protected $master = null;
    protected $cache;

    public function __construct($name)
    {
        $this->cache = app()['syncer.cache'];
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setMaster(Server $server)
    {
        $this->master = $server;
        return $this;
    }

    public function getBalanceKey(string $key) : string
    {
        return sprintf('%s.%.s.balance', app()['syncer.master']->getName(), $key);
    }

    public function getReservedKey(string $key) : string
    {
        return sprintf('%s.%s.reserved', $this->name, $key);
    }

    public function getBalance(string $key) : int
    {
        if ($this->master) {
            return $this->master->getBalance($key);
        }
        $function =$this->getDefaultBalance();
        return $function($key);
    }

    public function getOrderReservKey(string $orderId, string $key)
    {
        return sprintf('%s.%s.%s.reserved', $this->name, $orderId, $key);
    }

    public function setBalance($key, $value): self
    {
        $this->cache->put($this->getBalanceKey($key), $value);
        return $this;
    }

    public function getReserved(string $key) : int
    {
        return $this->cache->get($this->getReservedKey($key), 0);
    }

    public function getOrderReserved(string $orderId, string $key)
    {
        return $this->cache->get($this->getOrderReservKey($orderId, $key));
    }

    public function getAvailable(string $key) : int
    {
        return $this->getBalance($key) - $this->getReserved($key);
    }

    public function reserve(string $key, string $orderId, int $quantity = 1)
    {
        if ($this->getAvailable($key) < $quantity) {
            return false;
        }

        $this->cache->increment($this->getOrderReservKey($orderId, $key), $quantity);
        $this->cache->increment($this->getReserved($key), $quantity);

        return true;
    }

    public function dereserve(string $key, string $orderId, int $quantity = 1)
    {
        if ($this->getOrderReserved($orderId, $key) < $quantity) {
            return false;
        }

        $this->cache->decrement($this->getOrderReservKey($orderId, $key), $quantity);
        $this->cache->decrement($this->getReserved($key), $quantity);

        return true;
    }

    private function getDefaultBalance()
    {
        return function ($key) {
            return $this->cache->get($this->getBalanceKey($key), 0);
        };
    }
}
