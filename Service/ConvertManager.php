<?php

namespace Xigen\Bundle\CurrencyConverterBundle\Service;

use GuzzleHttp\Client;
use Psr\Cache\CacheItemPoolInterface;

class ConvertManager
{
    const CACHE_KEY = 'currency_converter.convert.rates';

    /**
     * Access key
     * @var string
     */
    private $accessKey = 'b8b61adb8f4486f53081c9f3b21f5dc5';

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

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
     * Fetch and then update the local cache. Cached data will last 4 hours
     * @return array
     */
    public function updateLocalCache()
    {
        $soruceData = $this->fetchSourceData();

        if (false === $soruceData) {
            return false;
        }

        $dataArray = \GuzzleHttp\json_decode($soruceData->getContents(), true);

        // Check when the API isn't working correctly
        if (false === $dataArray['success']) {
            return false;
        }

        $rates = $dataArray['rates'];
        $cachedRates = $this->cache->getItem(self::CACHE_KEY);
        $cachedRates->expiresAfter(43200);
        $cachedRates->set($rates);
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
        if (false === $rates) {
            return false;
        }

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
        try {
            $client = new Client();
            $request = $client->request(
                'GET',
                "https://data.fixer.io/api/latest?base=GBP&access_key={$this->accessKey}"
            );
        } catch (\Exception $e) {
            return false;
        }

        return $request->getBody();
    }
}
