<?php

namespace Xigen\Bundle\CurrencyConverterBundle\Service;

use GuzzleHttp\Client;
use Psr\Cache\CacheItemPoolInterface;

class ConvertManager
{
    const CACHE_KEY = 'currency_converter.convert.rates';

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Convert to GBP from another currency
     * @param  string $currency
     * @param  int $amount
     * @return float
     */
    public function to($currency, int $amount)
    {
        $currency = strtoupper($currency);
        $rate = $this->fetchRate($currency);

        return round($amount * $rate, 2);
    }

    /**
     * Convert from GBP to another currency
     * @param  string $currency
     * @param  int $amount
     * @return float
     */
    public function from($currency, int $amount)
    {
        $currency = strtoupper($currency);
        $rate = $this->fetchRate($currency);

        return round($amount / $rate, 2);
    }

    /**
     * Fetch and then update the local cache
     * @return array
     */
    public function updateLocalCache()
    {
        $soruceData = $this->fetchSourceData();
        $dataArray = \GuzzleHttp\json_decode($soruceData->getContents(), true);

        $rates = $dataArray['rates'];
        $cachedRates = $this->cache->getItem(self::CACHE_KEY);
        $cachedRates->set($dataArray['rates']);
        $this->cache->save($cachedRates);

        return $rates;
    }

    /**
     * Fetch the rate for a currency
     * @param  string $currency
     * @return float
     */
    protected function fetchRate($currency)
    {
        $rates = $this->fetchRates();
        if (array_key_exists($currency, $rates)) {
            return $rates[$currency];
        }

        return false;
    }

    /**
     * Fetch rates from the cache
     * @return array
     */
    protected function fetchRates()
    {
        $cachedRates = $this->cache->getItem(self::CACHE_KEY);
        if (true === $cachedRates->isHit()) {
            return $cachedRates->get();
        }

        return $this->updateLocalCache();
    }

    /**
     * Fetch the latest rates from fixer.io
     * @return GuzzleHttp\Psr7\Stream
     */
    private function fetchSourceData()
    {
        $client = new Client();
        $request = $client->request(
            'GET',
            'https://api.fixer.io/latest?base=GBP'
        );

        // TODO Add some error checking here if the API fails

        return $request->getBody();
    }
}
