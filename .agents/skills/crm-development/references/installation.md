# CRM installation

- Confirm installed versions and authenticated root VCS repositories for CRM, Audit and the bridge. Requires PHP 8.5+, Laravel 13.35+ and a configured Eloquent WorkspaceUser with integer id and UUID. User administration is optional.

- Let the provider load CRM migrations; never copy/publish them. Foundation owns users, roles/permissions and Activitylog schemas. Use only fresh disposable databases to verify this pre-release baseline; import no old records.

- Set crm.middleware to the host's web/auth/active-user/verified stack. Selectors and organization/contact routes also reject inactive users; CRUD routes require crm.view. DTO structure caching is integrated. Setting crm.enabled=false before boot removes contributions/routes; rebuild caches afterward. Tables/history are retained on removal.

Use the package README for complete installation requirements. Validate against a fresh disposable consumer; preserve existing host data.
