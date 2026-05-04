<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SellerAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        return view('frontend.seller.analytics');
    }
}
