<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use Symfony\Component\Intl\Intl;

/**
 * Class CurrencyService
 * @package MobileCart\CoreBundle\Service
 */
class CurrencyService implements CurrencyServiceInterface
{
    // todo: integrate more of:  http://symfony.com/doc/current/components/intl.html

    /**
     * @var array
     */
    protected $rateMap = [];

    /**
     * r['USD/EUR'] = ['dec_point' => '.', 'thousands_sep' => ',']
     *
     * @var array
     */
    protected $displayMap = [];

    /**
     * @var string
     */
    protected $baseCurrency = 'USD';

    /**
     * @var int
     */
    protected $baseBeforeAfter = -1;

    /**
     * @var string
     */
    protected $baseDecimalPoint = '.';

    /**
     * @var string
     */
    protected $baseThousandsSep = ',';

    /**
     * @var int
     */
    protected $baseMultiplierPrecision = 4;

    /**
     * @param $to
     * @return bool
     */
    public function hasRate($to)
    {
        if ($to == $this->baseCurrency) {
            return true;
        }

        $code = "{$this->baseCurrency}/{$to}";
        return isset($this->rateMap[$code]);
    }

    /**
     * @param $fromTo
     * @param $multiplier
     * @param int $multiplyPrecision
     * @param string $decPoint
     * @param string $thousandsSep
     * @param $beforeAfter
     * @return $this
     */
    public function addRate($fromTo, $multiplier, $multiplyPrecision = 4, $decPoint = '.', $thousandsSep = ',', $beforeAfter = -1)
    {
        $this->rateMap[$fromTo] = $multiplier;
        $this->displayMap[$fromTo] = [
            self::MULTIPLY_PRECISION => $multiplyPrecision,
            self::DEC_POINT => $decPoint,
            self::THOUSANDS_SEP => $thousandsSep,
            self::BEFORE_AFTER => $beforeAfter,
        ];
        return $this;
    }

    /**
     * @param $currency
     * @return string
     */
    public function getSymbol($currency)
    {
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * @param $code
     * @return $this
     */
    public function setBaseCurrency($code)
    {
        $this->baseCurrency = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * @return string
     */
    public function getBaseSymbol()
    {
        return $this->getSymbol($this->getBaseCurrency());
    }

    /**
     * @return int|null
     */
    public function getBaseDisplayedPrecision()
    {
        return $this->getDisplayedPrecision($this->getBaseCurrency());
    }

    /**
     * @param $currency
     * @return int|null
     */
    public function getDisplayedPrecision($currency)
    {
        return Intl::getCurrencyBundle()->getFractionDigits($currency);
    }

    /**
     * @param $precision
     * @return $this
     */
    public function setBaseMultiplierPrecision($precision)
    {
        $this->baseMultiplierPrecision = $precision;
        return $this;
    }

    /**
     * @param $decPoint
     * @return $this
     */
    public function setBaseDecimalPoint($decPoint)
    {
        $this->baseDecimalPoint = $decPoint;
        return $this;
    }

    /**
     * @param $sep
     */
    public function setBaseThousandsSep($sep)
    {
        $this->baseThousandsSep = $sep;
    }

    /**
     * @param $fromTo
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function quote($fromTo)
    {
        // compatible with other libraries eg
        //  https://github.com/florianv/swap

        if (!isset($this->rateMap[$fromTo])) {
            throw new \InvalidArgumentException("Invalid currency code: {$fromTo}");
        }
        return $this->rateMap[$fromTo];
    }

    /**
     * Handy proxy method
     *  maybe easier in templates; via a Twig Extension
     *
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getRate($from, $to)
    {
        return $this->quote("{$from}/{$to}");
    }

    /**
     * @param $from
     * @param $to
     * @param $value
     * @return string
     */
    public function convert($value, $to = '', $from = '')
    {
        if (!$from) {
            $from = $this->baseCurrency;
        }

        if (!$to) {
            $to = $this->baseCurrency;
        }

        $multiplierPrecision = $this->baseMultiplierPrecision;
        if (isset($this->displayMap["{$from}/{$to}"])) {
            $displayData = $this->displayMap["{$from}/{$to}"];
            $multiplierPrecision = $displayData[self::MULTIPLY_PRECISION];
        } else {
            $from = $this->baseCurrency;
            $to = $this->baseCurrency;
        }

        if ($from == $to) {
            return number_format((float) $value, $multiplierPrecision, '.', '');
        }

        // could round up or down here
        $rate = number_format((float) $this->getRate($from, $to), $multiplierPrecision, '.', '');
        $value = number_format((float) $value, $multiplierPrecision, '.', '');
        $result = $rate * $value;
        // could round up or down here
        return $result;
    }

    /**
     * @param $to
     * @param $value
     * @param string $from
     * @return string
     */
    public function decorate($value, $to = '', $from = '')
    {
        if (!$from) {
            $from = $this->getBaseCurrency();
        }

        if (!$to) {
            $to = $this->getBaseCurrency();
        }

        $result = $this->convert($value, $to, $from);

        // from Intl component
        $symbol = $this->getBaseSymbol();
        $displayedPrecision = $this->getBaseDisplayedPrecision();

        // from custom logic
        $decPoint = $this->baseDecimalPoint;
        $thousandsSep = $this->baseThousandsSep;
        $beforeAfter = $this->baseBeforeAfter;

        if (isset($this->displayMap["{$from}/{$to}"])) {

            // from Intl component
            $symbol = $this->getSymbol($to);
            $displayedPrecision = $this->getDisplayedPrecision($to);

            // from custom logic
            $displayData = isset($this->displayMap["{$from}/{$to}"])
                ? $this->displayMap["{$from}/{$to}"]
                : [];

            $decPoint = isset($displayData[self::DEC_POINT])
                ? $displayData[self::DEC_POINT]
                : $decPoint;

            $thousandsSep = isset($displayData[self::THOUSANDS_SEP])
                ? $displayData[self::THOUSANDS_SEP]
                : $thousandsSep;

            $beforeAfter = isset($displayData[self::BEFORE_AFTER])
                ? $displayData[self::BEFORE_AFTER]
                : $beforeAfter;

        }

        $value = number_format((float) $result, $displayedPrecision, $decPoint, $thousandsSep);
        return $beforeAfter
            ? $symbol . $value
            : $value . $symbol;
    }
}
