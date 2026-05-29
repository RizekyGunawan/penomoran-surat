# Penomoran Surat

> A web-based application for managing official document numbering in a government organization. Built to replace manual workflows with an automated, auditable, and role-based system.

# Features
- Automatic Sequential Numbering — Unique document numbers generated per type, per year
- 9 Document Types — Official letters, travel orders, invitations, circulars, and more
- Role-Based Access Control — 4 roles: Admin, Staff, Unit Secretary, Central Correspondence
- SSO Integration — Single Sign-On authentication with local login fallback
- Audit Logging — Every action (create, edit, delete, cancel) is tracked
- Print Templates — Ready-to-print output for most document types
- Access Request Workflow — Unit-level access requests with approval process
- Dockerized — Containerized deployment with Docker Compose and Jenkins CI/CD
---

# Project Structure
```
app/
├── Controllers/       # Auth, Penomoran, Admin, TU Unit, TU Persuratan
├── Models/            # Data models with audit callbacks
├── Filters/           # Route-level RBAC middleware
├── Libraries/         # SSO integration service
├── Services/          # Business logic (organizational hierarchy)
├── Views/             # Server-rendered templates
└── Database/
    └── Migrations/    # 17 versioned schema migrations
