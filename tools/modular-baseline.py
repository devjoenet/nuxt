#!/usr/bin/env python3
"""Inventory a committed source tree without booting it or reading runtime secrets.

Usage: python3 tools/modular-baseline.py REVISION OUTPUT_DIRECTORY
Outputs are deterministic for a revision and this ownership ruleset.
"""

import csv
import hashlib
import json
import re
import subprocess
import sys
from collections import Counter
from pathlib import Path


def git(*args):
    return subprocess.check_output(["git", *args])


def ownership(path):
    """Primary destination; split notes describe cross-domain integration files."""
    lower = path.lower()
    name = Path(path).name
    if "migrations/" in path:
        if any(x in lower for x in ["correct_staff", "clear_assumed", "backfill_approved", "normalize_development", "revoke_crm", "rename_development", "add_development_workspace_permissions", "retire_component_gallery"]):
            return "archive", "obsolete record/permission correction; seed current definitions instead"
        if "activity_log" in lower or "permission_tables" in lower or "users_table" in lower or "two_factor_columns" in lower:
            return "starter", "single shared schema authority; invitation tracking consumed by user-management"
        if "development" in lower or "user_testing" in lower:
            return "development", "fresh schema; retain legacy testing functionality"
        if any(x in lower for x in ["crm", "contact", "organizations", "leads", "follow_up"]):
            return "crm", "fresh schema including service-interest/intake columns; no old records"
        return "starter", "foundation schema"
    if path in ["routes/web.php", "routes/auth.php", "routes/console.php", "app/Providers/AppServiceProvider.php", "app/Queries/RecentAdminChangesQuery.php", "app/Enums/Permission.php", "app/Enums/PermissionGroup.php", "app/Enums/RoleName.php", "database/seeders/DatabaseSeeder.php", "database/seeders/RolePermissionSeeder.php", "app/Models/User.php", "resources/js/composables/useWorkspaceNavigation.ts", "resources/js/composables/useWorkspaceBreadcrumbs.ts", "resources/js/types/index.d.ts"]:
        return "starter", "SPLIT: foundation shell; bridge contributions; domain routes/policies/permissions/presenters stay with domain"
    if path.startswith((".ai/", ".agents/", ".github/", "tools/")) or path in ["vite.config.ts", "tsconfig.json", "composer.json", "composer.lock", "package.json", "yarn.lock", "components.d.ts", "auto-imports.d.ts"]:
        return "starter", "tooling/source guidance; adapt bridge discovery and independent consumer coverage before extraction"
    if path.startswith("docs/") or path.startswith("tests/") and any(x in name for x in ["Documentation", "DomainBoundary", "FrontendArchitecture", "AdminOverview", "Dashboard", "Navigation", "RecordHistoryNavigation", "Launch", "ServerSideRendering"]):
        return "agency", "retained integration/evidence contract; reusable runbook/test portions also copied to owning repositories"
    if path.startswith("resources/img/") or any(x in path for x in ["PublicIntake", "IntakeData", "SubmitPublicIntake", "NewLeadReceived", "ServiceInterest", "pages/Welcome", "pages/Privacy", "Landing", "PrivacyPolicy", "PrivacyDialog", "AppLogo", "TeamsMenu/AppLogoIcon"]):
        return "agency", "approved host content/intake/branding; replace branding in starter"
    if path.startswith("public/"):
        if name in ["index.php", "theme-bootstrap.js", ".htaccess"]:
            return "starter", "runtime entry/theme bootstrap"
        return "agency", "asset/content approval required; no inferred redistribution rights"
    if any(x in path for x in ["Development", "UserTesting", "user-testing", "types/development"]):
        return "development", "retain workflow, legacy pages, schema and regression coverage"
    if any(x in path for x in ["ActivityLog", "ActivityPolicy", "AuditIndex", "Admin/Audit", "RecordHistoryController", "RecordHistoryQuery", "RecordHistoryTable", "RecordHistoryButton", "pages/History/", "RecordHistoryTest", "AdminAudit"]):
        return "audit", "browsing/history API; underlying activity storage remains starter-owned"
    if any(x in path for x in ["Crm", "Contact", "Organization", "Lead", "FollowUp", "NoteFactory", "NotePolicy", "Models/Note.php", "TaskPriority", "types/crm"]):
        note = "CRM domain; resolve configured workspace users and public UI/transport API"
        if "CreateLeadFromIntake" in path:
            note = "SPLIT: CRM creation DTO/action; host maps IntakeData, settings and notification"
        if "TaskPriority" in path:
            note = "SPLIT: CRM and Development consume this enum; establish a declared shared contract"
        return "crm", note
    if any(x in path for x in ["RolePermission", "RolePermissions", "CreateRole", "RoleController", "RolePolicy", "StoreRole", "UpdateRole", "Admin/Roles", "Admin/Users", "AdminUser", "InviteUser", "UserInvitation", "AcceptUserInvitation", "UserPolicy", "ResendUserVerification", "ResetUserTwoFactor", "AdminStateActions", "AdminAuthorization", "AdminRoleEditor"]):
        return "user-management", "administration only; self-service auth, User schema and initial admin stay starter-owned"
    return "starter", "foundation/shared UI/runtime tooling; preserve reusable source and notices"


def write_tsv(output, name, fields, rows):
    with (output / name).open("w", newline="") as handle:
        writer = csv.writer(handle, delimiter="\t", lineterminator="\n")
        writer.writerow(fields)
        writer.writerows(rows)


def main():
    revision = git("rev-parse", f"{sys.argv[1]}^{{commit}}").decode().strip()
    output = Path(sys.argv[2])
    output.mkdir(parents=True, exist_ok=True)
    entries = [entry.split(b"\t", 1) for entry in git("ls-tree", "-rz", revision).split(b"\0") if entry]
    blobs = [(path.decode(), meta.split()[2]) for meta, path in entries if meta.split()[1] == b"blob"]
    # Read Git blobs directly: git archive would omit export-ignore documentation.
    batch = subprocess.check_output(["git", "cat-file", "--batch"], input=b"\n".join(oid for _, oid in blobs) + b"\n")
    files, offset = {}, 0
    for path, _ in blobs:
        end = batch.index(b"\n", offset)
        size = int(batch[offset:end].split()[2])
        files[path] = batch[end + 1:end + 1 + size]
        offset = end + size + 2
    inventory, imports, definitions, schemas = [], [], [], []
    for path, content in sorted(files.items()):
        owner, decision = ownership(path)
        inventory.append([path, owner, decision, hashlib.sha256(content).hexdigest(), len(content)])
        if Path(path).suffix not in [".php", ".ts", ".vue", ".js", ".mjs"]:
            continue
        for number, line in enumerate(content.decode(errors="replace").splitlines(), 1):
            if path.startswith("database/migrations/") and re.search(r"Schema::|\$table->", line):
                schemas.append([path, number, owner, line.strip()])
            if re.search(r"^use |\bfrom\s*['\"]|\bimport\s*['\"]|\bimport\(", line):
                imports.append([path, number, owner, line.strip()])
            if re.search(r"morph|subject_type|causer_type|Route::|Schedule::|Gate::policy|Inertia::render|structure_caching|app_path\('Data'", line, re.I):
                definitions.append([path, number, owner, line.strip()])
    write_tsv(output, "ownership.tsv", ["path", "owner", "decision", "sha256", "bytes"], inventory)
    write_tsv(output, "imports.tsv", ["path", "line", "owner", "source"], imports)
    write_tsv(output, "contracts.tsv", ["path", "line", "owner", "source"], definitions)
    write_tsv(output, "schemas.tsv", ["path", "line", "owner", "source"], schemas)
    summary = {
        "revision": revision,
        "tree": git("rev-parse", f"{revision}^{{tree}}").decode().strip(),
        "files": len(inventory),
        "owners": dict(sorted(Counter(row[1] for row in inventory).items())),
        "imports": len(imports),
        "contract_references": len(definitions),
        "categories": {category: [p for p in sorted(files) if p.startswith(prefix)] for category, prefix in {
            "models": "app/Models/", "policies": "app/Policies/", "providers": "app/Providers/",
            "jobs": "app/Jobs/", "notifications": "app/Notifications/", "commands": "app/Console/",
            "migrations": "database/migrations/", "dtos": "app/Data/", "config": "config/",
            "tests": "tests/", "public_assets": "public/", "image_assets": "resources/img/", "routes": "routes/",
        }.items()},
    }
    write_tsv(output, "categories.tsv", ["category", "path"], [
        [category, path] for category, paths in summary["categories"].items() for path in paths
    ])
    (output / "inventory.json").write_text(json.dumps({key: value for key, value in summary.items() if key != "categories"}, indent=2) + "\n")
    print(json.dumps({key: value for key, value in summary.items() if key != "categories"}, indent=2))


if __name__ == "__main__":
    main()
