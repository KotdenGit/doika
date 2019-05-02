<?php declare(strict_types = 1);

namespace Diglabby\Doika\Models;

use Carbon\CarbonInterval;
use Money\Money;

final class SubscriptionIntend
{
    /** @var Money */
    public $money;

    /** @var Donator */
    public $donator;

    /** @var Campaign */
    public $campaign;

    /** @var CarbonInterval */
    private $interval;

    public function __construct(Money $money, Donator $donator, Campaign $campaign, CarbonInterval $interval)
    {
        if ($campaign->isFinished()) {
            throw new \DomainException('Can not subscribe to a finished campaign');
        }

        $this->money = $money;
        $this->donator = $donator;
        $this->campaign = $campaign;
        $this->interval = $interval;
    }

    public static function weekly(Money $money, Donator $donator, Campaign $campaign): SubscriptionIntend
    {
        $interval = new CarbonInterval('P1W');

        return new self($money, $donator, $campaign, $interval);
    }

    public static function monthly(Money $money, Donator $donator, Campaign $campaign): SubscriptionIntend
    {
        $interval = new CarbonInterval('P1M');

        return new self($money, $donator, $campaign, $interval);
    }

    public function getPlannedTimesToPay(): int
    {
        $period = $this->interval->toPeriod(now(), $this->campaign->finished_at);
        return $period->count();
    }

    public function getPlanName(): string
    {
        $currencyCode = $this->money->getCurrency()->getCode();
        return ((int) $this->money->getAmount() / 100)."{$currencyCode} для {$this->campaign->name}";
    }
}
