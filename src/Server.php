<?php

namespace Sotoro\Syncer;

use Illuminate\Support\Facades\Cache;

class Server
{
    protected $name;
    protected $balance;
    protected $master = null;

    public function __construct($name)
    {
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
        Cache::put($this->getBalanceKey($key), $value);
        return $this;
    }

    public function getReserved(string $key) : int
    {
        return Cache::get($this->getReservedKey($key), 0);
    }

    public function getOrderReserved(string $orderId, string $key)
    {
        return Cache::get($this->getOrderReservKey($orderId, $key));
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

        Cache::increment($this->getOrderReservKey($orderId, $key), $quantity);
        Cache::increment($this->getReserved($key), $quantity);

        return true;
    }

    public function dereserve(string $key, string $orderId, int $quantity = 1)
    {
        if ($this->getOrderReserved($orderId, $key) < $quantity) {
            return false;
        }

        Cache::decrement($this->getOrderReservKey($orderId, $key), $quantity);
        Cache::decrement($this->getReserved($key), $quantity);

        return true;
    }

    private function getDefaultBalance()
    {
        return function ($key) {
            return Cache::get($this->getBalanceKey($key), 0);
        };
    }
}
