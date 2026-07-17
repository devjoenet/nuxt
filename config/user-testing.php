<?php

declare(strict_types=1);

return [
    'pages' => [
        ['slug' => 'public-welcome', 'category' => 'Public', 'title' => 'Welcome / landing page', 'route_name' => 'home', 'path' => '/', 'sort_order' => 10, 'notes' => 'Review every landing-page section, link, animation, responsive state, dark mode state, and the project inquiry form.'],
        ['slug' => 'public-privacy', 'category' => 'Public', 'title' => 'Privacy policy', 'route_name' => 'privacy', 'path' => '/privacy', 'sort_order' => 20],
        ['slug' => 'auth-login', 'category' => 'Authentication', 'title' => 'Log in', 'route_name' => 'login', 'path' => '/login', 'sort_order' => 30],
        ['slug' => 'auth-forgot-password', 'category' => 'Authentication', 'title' => 'Forgot password', 'route_name' => 'password.request', 'path' => '/forgot-password', 'sort_order' => 40],
        ['slug' => 'auth-reset-password', 'category' => 'Authentication', 'title' => 'Reset password', 'route_name' => 'password.reset', 'path' => '/reset-password/{token}', 'sort_order' => 50],
        ['slug' => 'auth-verify-email', 'category' => 'Authentication', 'title' => 'Verify email', 'route_name' => 'verification.notice', 'path' => '/verify-email', 'sort_order' => 60],
        ['slug' => 'auth-two-factor', 'category' => 'Authentication', 'title' => 'Two-factor challenge', 'route_name' => 'two-factor.login', 'path' => '/two-factor-challenge', 'sort_order' => 70],
        ['slug' => 'auth-confirm-password', 'category' => 'Authentication', 'title' => 'Confirm password', 'route_name' => 'password.confirm', 'path' => '/user/confirm-password', 'sort_order' => 80],
        ['slug' => 'workspace-dashboard', 'category' => 'Workspace', 'title' => 'Dashboard', 'route_name' => 'dashboard', 'path' => '/dashboard', 'sort_order' => 90],
        ['slug' => 'crm-leads-index', 'category' => 'CRM', 'title' => 'Leads list', 'route_name' => 'crm.leads.index', 'path' => '/crm/leads', 'sort_order' => 100],
        ['slug' => 'crm-leads-create', 'category' => 'CRM', 'title' => 'Create lead', 'route_name' => 'crm.leads.create', 'path' => '/crm/leads/create', 'sort_order' => 110],
        ['slug' => 'crm-leads-show', 'category' => 'CRM', 'title' => 'Lead detail', 'route_name' => 'crm.leads.show', 'path' => '/crm/leads/{lead}', 'sort_order' => 120],
        ['slug' => 'crm-leads-edit', 'category' => 'CRM', 'title' => 'Edit lead', 'route_name' => 'crm.leads.edit', 'path' => '/crm/leads/{lead}/edit', 'sort_order' => 130],
        ['slug' => 'crm-organizations-index', 'category' => 'CRM', 'title' => 'Organizations list', 'route_name' => 'crm.organizations.index', 'path' => '/crm/organizations', 'sort_order' => 140],
        ['slug' => 'crm-organizations-create', 'category' => 'CRM', 'title' => 'Create organization', 'route_name' => 'crm.organizations.create', 'path' => '/crm/organizations/create', 'sort_order' => 150],
        ['slug' => 'crm-organizations-show', 'category' => 'CRM', 'title' => 'Organization detail', 'route_name' => 'crm.organizations.show', 'path' => '/crm/organizations/{organization}', 'sort_order' => 160],
        ['slug' => 'crm-organizations-edit', 'category' => 'CRM', 'title' => 'Edit organization', 'route_name' => 'crm.organizations.edit', 'path' => '/crm/organizations/{organization}/edit', 'sort_order' => 170],
        ['slug' => 'crm-contacts-index', 'category' => 'CRM', 'title' => 'Contacts list', 'route_name' => 'crm.contacts.index', 'path' => '/crm/contacts', 'sort_order' => 180],
        ['slug' => 'crm-contacts-create', 'category' => 'CRM', 'title' => 'Create contact', 'route_name' => 'crm.contacts.create', 'path' => '/crm/contacts/create', 'sort_order' => 190],
        ['slug' => 'crm-contacts-show', 'category' => 'CRM', 'title' => 'Contact detail', 'route_name' => 'crm.contacts.show', 'path' => '/crm/contacts/{contact}', 'sort_order' => 200],
        ['slug' => 'crm-contacts-edit', 'category' => 'CRM', 'title' => 'Edit contact', 'route_name' => 'crm.contacts.edit', 'path' => '/crm/contacts/{contact}/edit', 'sort_order' => 210],
        ['slug' => 'admin-dashboard', 'category' => 'Administration', 'title' => 'Admin dashboard', 'route_name' => 'admin.dashboard', 'path' => '/admin', 'sort_order' => 220],
        ['slug' => 'admin-users', 'category' => 'Administration', 'title' => 'User management', 'route_name' => 'admin.users.index', 'path' => '/admin/users', 'sort_order' => 230],
        ['slug' => 'admin-roles', 'category' => 'Administration', 'title' => 'Role management', 'route_name' => 'admin.roles.index', 'path' => '/admin/roles', 'sort_order' => 240],
        ['slug' => 'admin-jobs', 'category' => 'Administration', 'title' => 'Jobs and queues', 'route_name' => 'admin.jobs.index', 'path' => '/admin/jobs', 'sort_order' => 250],
        ['slug' => 'admin-settings', 'category' => 'Administration', 'title' => 'Business settings', 'route_name' => 'admin.settings.edit', 'path' => '/admin/settings', 'sort_order' => 260],
        ['slug' => 'admin-audit', 'category' => 'Administration', 'title' => 'Audit log', 'route_name' => 'admin.audit.index', 'path' => '/admin/audit', 'sort_order' => 270],
        ['slug' => 'settings-profile', 'category' => 'Settings', 'title' => 'Profile settings', 'route_name' => 'profile.edit', 'path' => '/settings/profile', 'sort_order' => 280],
        ['slug' => 'settings-security', 'category' => 'Settings', 'title' => 'Security settings', 'route_name' => 'security.show', 'path' => '/settings/security', 'sort_order' => 290],
        ['slug' => 'shared-error-pages', 'category' => 'Shared states', 'title' => 'Error pages', 'route_name' => null, 'path' => '403 / 404 / 419 / 429 / 500 / 503', 'sort_order' => 300],
    ],
];
