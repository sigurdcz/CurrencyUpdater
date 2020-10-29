<?php 
declare(strict_types = 1); 
namespace Gnezdilov\CurrencyUpdater\Model;

class CurrencyIteratorItem
{

    private $country, $currency, $amount, $code, $rate;

    function __construct(string $country, string $currency, int $amount, string $code, float $rate)
    {
        $this->country = $country;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->code = $code;
        $this->rate = $rate;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
    public function getCurrency(): string
    {
        return $this->currency;
    }
    public function getAmount(): int
    {
        return $this->amount;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getRate(): float
    {
        return $this->rate;
    }
}