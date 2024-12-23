<?php

namespace App\Services\Payout;

use App\Contracts\PayoutServiceContract;
use App\Models\PayoutOffer;

class PayoutService implements PayoutServiceContract
{
    public function create()
    {

    }

    public function getOffersMenu(): array
    {
        $groupedPayoutOffers = PayoutOffer::query()
            ->with('paymentGateway', 'owner')
            ->where('active', true)
            ->get()
            ->mapToGroups(function (PayoutOffer $payoutOffer) {
                return [$payoutOffer->paymentGateway->code => $payoutOffer];
            });

        $aggregatedOffers = [];

        foreach ($groupedPayoutOffers as $key => $payoutOffers) {
            foreach ($payoutOffers as $payoutOffer) {
                /**
                 * @var PayoutOffer $payoutOffer
                 */
                if (empty($aggregatedOffers[$key])) {
                    $aggregatedOffers[$key] = [
                        'max_amount' => $payoutOffer->max_amount,
                        'min_amount' => $payoutOffer->min_amount,
                        'currency' => $payoutOffer->currency->getCode(),
                        'detail_type' => $payoutOffer->detail_types->first()->value,
                        'payment_gateway' => [
                            'name' => $payoutOffer->paymentGateway->name,
                            'name_with_currency' => $payoutOffer->paymentGateway->name_with_currency,
                            'code' => $payoutOffer->paymentGateway->code,
                        ],
                        'offers_count' => 1
                    ];
                }

                if ($aggregatedOffers[$key]['max_amount']->lessThan($payoutOffer->max_amount)) {
                    $aggregatedOffers[$key]['max_amount'] = $payoutOffer->max_amount;
                }
                if ($aggregatedOffers[$key]['min_amount']->lessThan($payoutOffer->min_amount)) {
                    $aggregatedOffers[$key]['min_amount'] = $payoutOffer->min_amount;
                }

                $aggregatedOffers[$key]['offers_count'] += 1;
            }
        }

        foreach ($groupedPayoutOffers as $key => $payoutOffers) {
            foreach ($payoutOffers as $payoutOffer) {
                $aggregatedOffers[$key]['recommended_max_amount'][] = intval($payoutOffer->max_amount->toBeauty());
                $aggregatedOffers[$key]['recommended_min_amount'][] = intval($payoutOffer->min_amount->toBeauty());
            }
            if (count($aggregatedOffers[$key]['recommended_max_amount']) <= 2) {
                $aggregatedOffers[$key]['recommended_max_amount'] = $aggregatedOffers[$key]['recommended_max_amount'][0];
                $aggregatedOffers[$key]['recommended_min_amount'] = $aggregatedOffers[$key]['recommended_min_amount'][0];
                continue;
            }
            sort($aggregatedOffers[$key]['recommended_max_amount']);
            sort($aggregatedOffers[$key]['recommended_min_amount']);

            $aggregatedOffers[$key]['recommended_max_amount'] = $aggregatedOffers[$key]['recommended_max_amount'][intval(count($aggregatedOffers[$key]['recommended_max_amount']) / 2) + 1];
            $aggregatedOffers[$key]['recommended_min_amount'] = $aggregatedOffers[$key]['recommended_min_amount'][intval(count($aggregatedOffers[$key]['recommended_min_amount']) / 2) + 1];
        }


        foreach ($aggregatedOffers as $key => $offer) {
            $aggregatedOffers[$key]['max_amount'] = $offer['max_amount']->toBeauty();
            $aggregatedOffers[$key]['min_amount'] = $offer['min_amount']->toBeauty();
        }

        return $aggregatedOffers;
    }

    public function makeOffersMenu(): void
    {

    }
}
