# ADR 0003: Scope CRM Visibility By Ownership

- Status: Accepted
- Date: 2026-07-15
- Decision point: DP-06

## Context

Sales/Ops users need a workable CRM queue without seeing every organization's, contact's, or lead's data. Admins need
to manage ownership without weakening direct-URL privacy.

## Decision

Each CRM record uses one nullable `owner_id`. Sales/Ops users can see records they own or records that are unassigned.
Admin and Super Admin users bypass ownership filters. Only Admin and Super Admin users can assign or transfer ownership.
Out-of-scope direct URLs return `404`; visible records with an unauthorized mutation return `403`.

## Consequences

- Unassigned work remains claimable by the operating team.
- Ownership is simple to query, audit, and explain in the UI.
- Cross-team sharing and multi-company tenancy remain deferred.
- Any new CRM entity must adopt the same visibility and direct-URL contract before it ships.
