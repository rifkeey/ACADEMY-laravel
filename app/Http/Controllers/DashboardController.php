<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, LearningService $learning): View
    {
        return view('learner.dashboard', $learning->dashboard($request->user()));
    }

    /** The learner's own orders only. */
    public function payments(Request $request): View
    {
        return view('learner.payments', [
            'orders' => Order::where('user_id', $request->user()->id)->with('specialization:id,title')->latest()->get(),
        ]);
    }
}
