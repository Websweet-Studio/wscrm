<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

abstract class CustomerBaseController extends Controller
{
    /**
     * Customer yang sedang login (guard 'customer').
     */
    protected function customer(): ?\App\Models\Customer
    {
        return auth('customer')->user();
    }

    /**
     * ID customer yang sedang login.
     */
    protected function customerId(): ?int
    {
        return auth('customer')->id();
    }
}
