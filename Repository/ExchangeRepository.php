<?php

namespace Xigen\Bundle\CurrencyConverterBundle\Repository;

use GuzzleHttp\Client;
use Xigen\Bundle\CurrencyConverterBundle\Entity\Exchange;

/**
 * ExchangeRepository
 */
class ExchangeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Fetch the latest rates from fixer.io
     * @return GuzzleHttp\Psr7\Stream
     */
    public function fetchSourceData()
    {
        $client = new Client();
        $request = $client->request(
            'GET',
            'https://api.fixer.io/latest?base=GBP'
        );

        return $request->getBody();
    }

    /**
     * Fetch and then update the local exchange values
     * @return bool
     */
    public function updateLocalData()
    {
        $em = $this->getEntityManager();
        $soruceData = $this->fetchSourceData();
        $dataArray = \GuzzleHttp\json_decode($soruceData->getContents(), true);

        foreach ($dataArray['rates'] as $currency => $rate) {
            $localCurrency = $this->findOneBy(['currency' => $currency]);
            if (null == $localCurrency) {
                $localCurrency = (new Exchange())
                    ->setCurrency($currency)
                ;
            }

            $localCurrency->setRate($rate);
            $em->persist($localCurrency);
        }
        $em->flush();

        return true;
    }

    /**
     * Convert from GBP to another currency
     * @param  string $currency
     * @param  integer $amount
     * @return float
     */
    public function convertTo($currency, integer $amount)
    {
        $em = $this->getEntityManager();
        $localCurrency = $this->findOneBy(['currency' => strtoupper($currency)]);

        return $amount * $localCurrency->getRate();
    }
}
