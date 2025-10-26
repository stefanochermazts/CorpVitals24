<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

/**
 * Company Policy for multi-tenant authorization.
 * 
 * Ensures users can only access companies within their team scope.
 */
class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Users can view companies in their team
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        // User can only view companies in their team
        return $user->belongsToTeam($company->team_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Managers and admins can create companies
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        // User must belong to the same team and have manager role
        return $user->belongsToTeam($company->team_id) && 
               $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only admins can delete companies
        return $user->belongsToTeam($company->team_id) && 
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return $user->belongsToTeam($company->team_id) && 
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $user->belongsToTeam($company->team_id) && 
               $user->hasRole('admin');
    }
}
