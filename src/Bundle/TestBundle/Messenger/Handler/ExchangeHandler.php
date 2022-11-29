<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\Messenger\Handler;

use App\Bundle\TestBundle\Messenger\Message\ExchangeMessage;
use App\Bundle\TestBundle\Model\Input;
use App\Bundle\TestBundle\Services\BinService;
use App\Bundle\TestBundle\Services\CalculateService;
use App\Bundle\TestBundle\Services\RatesService;
use JsonException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function is_array;
use function is_string;

class ExchangeHandler
{
    /**
     * @var CalculateService
     */
    private $calculateService;

    private $binService;
    private $ratesService;

    public function __construct(CalculateService $calculateService)
    {
        $this->calculateService = $calculateService;
        $this->setBinService(new BinService());
        $this->setRatesService(new RatesService());
    }

    public function __invoke(ExchangeMessage $arg)
    {
        $filename = $arg->getFileName();
        if (!file_exists($filename) && !is_readable($filename)) throw new FileNotFoundException();

        $file = file_get_contents($filename);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        $array = explode(PHP_EOL, $file);
        $calcParams = $this->calculateService->getParams();
        $rateArr = [];
        foreach ($array as $key => $val) {
            if (!$this->json_validator($val) || json_last_error() > JSON_ERROR_NONE) throw new JsonException('JSON: ' . json_last_error_msg());
            $json = json_decode($val);

            $input = $serializer->denormalize($json, Input::class, 'json');


            $binProvider = $calcParams['bins_provider'];
            $country = $this->getCountry($binProvider['url'] . '/' . $input->getBin(), $binProvider['auth'], $binProvider['mapping']);
            if (!$country) throw new NoSuchPropertyException();

            $ratesProvider = $calcParams['rates_provider'];
            $rates = $this->getRates($ratesProvider['url'], $ratesProvider['auth'], $ratesProvider['mapping']);
            if (!$rates) throw new NoSuchPropertyException();

            $currency = $input->getCurrency();
            $amount = $input->getAmount();
            $isEU = CalculateService::isCountryEU($country);

            if ($currency !== $ratesProvider['currency'] || array_search($currency, $rates, true) > 0){
                $amount /= $rates[$currency] ?? 0;
            }

            $rateArr[$key] = $this->calcEURates($amount, $isEU);
        }

        return $rateArr;
    }

    private function json_validator($data)
    {
        if (!empty($data)) {
            return (is_string($data) && is_array(json_decode($data, true)));
        }
        return false;
    }

    private function getCountry($url, $auth, $mapping)
    {
        $binService = $this->getBinService();
        $binService->setUrl($url);
        $binService->setAuth($auth);

        return (new PropertyAccessor())->getValue($binService->getData(), $mapping);
    }

    /**
     * @return mixed
     */
    public function getBinService()
    {
        return $this->binService;
    }

    /**
     * @param mixed $binService
     */
    public function setBinService($binService): void
    {
        $this->binService = $binService;
    }

    private function getRates($url, $auth, $mapping)
    {
        $ratesService = $this->getRatesService();
        $ratesService->setUrl($url);
        $ratesService->setAuth($auth);

        return (new PropertyAccessor())->getValue($ratesService->getData(), $mapping);
    }

    /**
     * @return mixed
     */
    public function getRatesService()
    {
        return $this->ratesService;
    }

    /**
     * @param mixed $ratesService
     */
    public function setRatesService($ratesService): void
    {
        $this->ratesService = $ratesService;
    }

    private function calcEURates($amount, bool $isEU = false)
    {
        return bcdiv((string)ceil((float)bcmul(bcmul($isEU ? '0.01' : '0.02', (string)$amount, 3), '100', 3)), '100', 2);
    }
}