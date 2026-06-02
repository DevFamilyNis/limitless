<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionKey: string
{
    case ViewDashboard = 'view-dashboard';

    case ViewClients = 'view-clients';
    case ManageClients = 'manage-clients';

    case ViewProjects = 'view-projects';
    case ManageProjects = 'manage-projects';

    case ViewInvoices = 'view-invoices';
    case ManageInvoices = 'manage-invoices';

    case ViewTransactions = 'view-transactions';
    case ManageTransactions = 'manage-transactions';

    case ViewLeads = 'view-leads';
    case ManageLeads = 'manage-leads';

    case ViewIssues = 'view-issues';
    case ManageIssues = 'manage-issues';

    case ViewKpo = 'view-kpo';
    case ManageKpo = 'manage-kpo';

    case ManageCategories = 'manage-categories';
    case ManageTaxYears = 'manage-tax-years';
    case ManageSettings = 'manage-settings';

    case ManageUsers = 'manage-users';
    case ManageRoles = 'manage-roles';
}
