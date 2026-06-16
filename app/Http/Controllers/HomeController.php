<?php

namespace App\Http\Controllers;

use App\Models\{Role, User, Order, Status};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Middleware\Authorize;

class HomeController extends Controller
{
    public function index()
    {
        $title = "Home";
        
        $stats = [];
        if (auth()->user()->role_id == 1) {
            $stats = [
                'total_customers' => User::where('role_id', 2)->count(),
                'pending_orders' => Order::where('status_id', 2)->count(),      // status_id 2 = pending
                'approved_orders' => Order::where('status_id', 1)->count(),     // status_id 1 = approve
                'rejected_orders' => Order::where('status_id', 3)->count(),     // status_id 3 = rejected
            ];
        }

        return view("/home/index", compact("title", "stats"));
    }

    public function customers()
    {
        $this->authorize("is_admin");

        $title = "Customers";
        $customers = User::with("role")->get();

        return view("home/customers",  compact("title", "customers"));
    }
}
