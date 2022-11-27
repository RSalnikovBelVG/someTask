<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\Model;

class Input
{
    private $bin;
    private $amount;
    private $currency;

    /**
     * @return mixed
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * @param mixed $bin
     */
    public function setBin($bin): void
    {
        $this->bin = $bin;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }
}