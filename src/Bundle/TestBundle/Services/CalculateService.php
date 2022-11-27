<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\Services;

use App\Bundle\TestBundle\Model\Input;

class CalculateService
{

    /**
     * @var array
     */
    private $params;

    public const COUNTRIES = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
        'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public static function isCountryEU(string $country)
    {
        return \in_array($country, self::COUNTRIES);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

}