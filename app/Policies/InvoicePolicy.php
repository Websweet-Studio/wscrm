<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Admin can view all invoices
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, Invoice $invoice): bool
    {
        // If it's an admin user
        if ($user instanceof User) {
            return true;
        }
        
        // If it's a customer, check if the invoice belongs to them
        if ($user instanceof Customer) {
            return $invoice->customer_id === $user->id;
        }
        
        // Check if customer is authenticated via customer guard
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            return $invoice->customer_id === $customer->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Only admin can create invoices
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return true; // Only admin can update invoices
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return true; // Only admin can delete invoices
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}
