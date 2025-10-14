<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgendaPolicy
{
    /**
     * Determine whether the user can view agenda.
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('agenda.view');
    }

    /**
     * Determine whether the user can create agenda items.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('agenda.create');
    }

    /**
     * Determine whether the user can update agenda items.
     */
    public function update(User $user): bool
    {
        return $user->hasPermission('agenda.edit');
    }

    /**
     * Determine whether the user can delete agenda items.
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('agenda.delete');
    }

    /**
     * Determine whether the user can manage payments.
     */
    public function managePayments(User $user): bool
    {
        return $user->hasPermission('agenda.payment');
    }

    /**
     * Determine whether the user can access financial features.
     */
    public function accessFinancial(User $user): bool
    {
        return $user->hasPermission('agenda.financial');
    }

    /**
     * Determine whether the user can export agenda data.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('agenda.export');
    }

    /**
     * Determine whether the user can view financial dashboard.
     * Only owners and managers should have access.
     */
    public function viewFinancialDashboard(User $user): bool
    {
        return $user->hasPermission('agenda.financial') && 
               ($user->hasRole('owner') || $user->hasRole('manager'));
    }

    /**
     * Determine whether the user can manage cash balance.
     * Only owners and managers should be able to record cash transactions.
     */
    public function manageCashBalance(User $user): bool
    {
        return $user->hasPermission('agenda.financial') && 
               ($user->hasRole('owner') || $user->hasRole('manager'));
    }

    /**
     * Determine whether the user can make payments.
     * Owners, managers, and authorized staff can make payments.
     */
    public function makePayments(User $user): bool
    {
        return $user->hasPermission('agenda.payment') && 
               ($user->hasRole('owner') || $user->hasRole('manager') || $user->hasRole('staff'));
    }

    /**
     * Determine whether the user can manage receivables.
     * Only owners and managers should manage receivables.
     */
    public function manageReceivables(User $user): bool
    {
        return $user->hasPermission('agenda.financial') && 
               ($user->hasRole('owner') || $user->hasRole('manager'));
    }

    /**
     * Determine whether the user can view sensitive financial data.
     * Only owners should see complete financial information.
     */
    public function viewSensitiveFinancialData(User $user): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Determine whether the user can modify payment schedules.
     * Only owners and managers can modify payment schedules.
     */
    public function modifyPaymentSchedules(User $user): bool
    {
        return $user->hasPermission('agenda.edit') && 
               ($user->hasRole('owner') || $user->hasRole('manager'));
    }

    /**
     * Determine whether the user can access audit logs.
     * Only owners should access audit logs for security.
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Determine whether the user can generate financial reports.
     * Owners and managers can generate reports.
     */
    public function generateReports(User $user): bool
    {
        return $user->hasPermission('agenda.export') && 
               ($user->hasRole('owner') || $user->hasRole('manager'));
    }

    /**
     * Determine whether the user can view notifications.
     * All users with agenda access can view notifications.
     */
    public function viewNotifications(User $user): bool
    {
        return $user->hasPermission('agenda.view');
    }

    /**
     * Determine whether the user can manage system settings.
     * Only owners can manage system settings.
     */
    public function manageSystemSettings(User $user): bool
    {
        return $user->hasRole('owner');
    }
}
