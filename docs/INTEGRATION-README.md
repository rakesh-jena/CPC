# CPC Leadership — Integrated Laravel 8 Source Package

This is the merged **public portal + admin portal** source package for the CPC Leadership project. It is designed as an overlay on a normal Laravel 8 application, because the supplied admin and public packages are application source additions rather than a complete Laravel framework installation.

## Included

### Public portal
- `/` — research directory landing page
- `/leaders` — searchable/filterable leader directory
- `/leaders/{leader}` — leader profile
- Blade + custom responsive CSS + vanilla JS
- Server-side PostgreSQL search/filter/pagination
- Career history, education/training, selected network indicators and analytical/inferred indicators

### Admin portal
- `/admin/login`
- `/admin/dashboard`
- Leaders CRUD
- Career history CRUD
- Education/training CRUD
- Political network CRUD
- Inferred data CRUD
- CSV staging/import workflow with preview and approval
- Import history
- Data-quality dashboard
- User management
- Roles: `super_admin`, `reviewer`, `editor`, `analyst`
- Audit log
- Private import storage under Laravel `storage/app`
- Bulk deletion tools
- Bootstrap 5.3.3 + Bootstrap Icons for admin UI

## Database

The supplied schema is included at:
`docs/Schema_ProvincialLeadershipProject_2November.sql`

The source was checked against these production tables:
- `leaders`
- `career_history`
- `education_training`
- `political_networks`
- `inferred_data`

and the five staging tables:
- `biodata_import`
- `career_import`
- `education_import`
- `political_networks_import`
- `inferred_data_import`

The CRUD controllers use Query Builder because the supplied research tables do not have Laravel `created_at`/`updated_at` columns.

## Install into Laravel 8

1. Start with your existing Laravel 8 project.
2. Copy the package `app/`, `database/`, `public/`, `resources/`, and `routes/` directories into the Laravel project, preserving paths.
3. Do **not** overwrite `.env`.
4. The integrated `routes/web.php` already loads `routes/admin.php`.
5. Add the admin middleware to `app/Http/Kernel.php`:

```php
'admin.role' => \App\Http\Middleware\AdminRole::class,
```

6. Configure PostgreSQL in `.env`.
7. Import the supplied research schema if the database is not already populated:

```bash
psql -U YOUR_USER -d YOUR_DATABASE -f docs/Schema_ProvincialLeadershipProject_2November.sql
```

8. Run the Laravel admin migrations:

```bash
php artisan migrate
```

9. Create the first admin account by setting:

```env
ADMIN_NAME="CPC Administrator"
ADMIN_EMAIL="admin@your-domain.com"
ADMIN_PASSWORD="use-a-long-random-password"
```

Then run:

```bash
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
```

10. Change the seeded password after first login.

## Important architecture rule

Do not put CSV imports, private research files, backups, or other non-public uploads in `public/`. The admin import flow uses Laravel private storage (`storage/app`). Only deliberately published assets should be exposed through `public/`.

## Public-data boundary

The public profile intentionally does not expose the sensitive family/finance/property fields present in the underlying `leaders` table. The public profile currently exposes selected professional/network indicators and clearly labels inferred/analytical data as analytical rather than verified fact.

Before production publication, add a formal publication/approval flag if individual records need editorial clearance. The current schema does not contain a dedicated public-visibility field, so this package does not invent one.

## Validation performed

The integration combines the exact supplied admin v4 source with the supplied public frontend source and the supplied PostgreSQL schema. Route loading has been unified so both portals live in one Laravel application.
