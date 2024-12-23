<?php

namespace App\Http\Controllers\API\Payout;

use App\Http\Controllers\Controller;
use App\Models\PayoutOffer;
use Illuminate\Http\Request;

class PayoutOfferController extends Controller
{
    public function index()
    {
        $offersMenu = services()->payout()->getOffersMenu();

        return response()->success($offersMenu);
    }
}
