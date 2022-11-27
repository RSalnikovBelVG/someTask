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

    public function __construct(CalculateService $calculateService)
    {
        $this->calculateService = $calculateService;
    }

    public function __invoke(ExchangeMessage $arg)
    {
        $filename = $arg->getFileName();
        if (!file_exists($filename) && is_readable($filename)) throw new FileNotFoundException();

        $file = file_get_contents($filename);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        $array = explode(PHP_EOL, $file);
        $calcParams = $this->calculateService->getParams();
        $propertyAccessor = new PropertyAccessor();
        $rateArr = [];
        foreach ($array as $key => $val) {
            if (!$this->json_validator($val) || json_last_error() > JSON_ERROR_NONE) throw new JsonException('JSON: ' . json_last_error_msg());
            $json = json_decode($val);

            $input = $serializer->denormalize($json, Input::class, 'json');

            $binProvider = $calcParams['bins_provider'];
            $binService = new BinService($binProvider['url'] . '/' . $input->getBin(), $binProvider['auth']);
            $country = $propertyAccessor->getValue($binService->getData(), $binProvider['mapping']);

            $ratesProvider = $calcParams['rates_provider'];
            $ratesService = new RatesService($ratesProvider['url'], $ratesProvider['auth']);
            $rates = $propertyAccessor->getValue($ratesService->getData(), $ratesProvider['mapping']);

            $currency = $input->getCurrency();
            $amount = $input->getAmount();
            $isEU = CalculateService::isCountryEU($country);
            if ($currency !== $ratesProvider['currency'] || $rates[$currency] > 0) $amount /= $rates[$currency];

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

    private function calcEURates($amount, bool $isEU = false)
    {
        return ceil(($amount * ($isEU ? 0.01 : 0.02)) * 100) / 100;
    }
}