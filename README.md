
# CPC Leadership

A research platform for exploring the leadership, career backgrounds, education, and political relationships of the Chinese Communist Party (CPC).

The application combines a public-facing research portal with an administrative dashboard for managing leader records, reviewing imported data, and maintaining relationship datasets.

## Features

### Public Research Portal

- Browse and search CPC leader profiles.
- Filter and paginate leader listings.
- Switch between list and grid views.
- Display leader avatars with a default image when no avatar is available.
- View individual leader profiles and biographies.
- Explore career history and education/training records.
- Explore political networks and relationship information.
- Visualise relationships through interactive network graphs.

### Administration Dashboard

- Secure administrator login.
- Admin dashboard for managing research data.
- Manage leader profiles and related records.
- Manage career history and education/training entries.
- Manage political relationships and network data.
- Import CSV datasets.
- Review imported data.
- Manage administrative users and permissions.
- Maintain data integrity and audit records.

## Technology Stack

| Component | Technology |
|---|---|
| Backend | Laravel 8 |
| Programming Language | PHP |
| Frontend | Blade, HTML5, CSS3, JavaScript |
| Database | MySQL 8 |
| Network Visualisation | Cytoscape.js |
| Web Server | Apache |
| Server | AWS EC2 Ubuntu |
| Database Administration | phpMyAdmin |

## Project Structure

```text
cpc-leadership/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
│
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── index.php
│
├── resources/
│   └── views/
│       ├── admin/
│       ├── public/
│       └── layouts/
│
├── routes/
│   └── web.php
│
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
└── README.md
```

## Database Structure

The application works with leadership and relationship datasets.

### Main Data Entities

| Table | Description |
|---|---|
| `leaders` | Leader identity and profile information |
| `career_history` | Political appointments and career records |
| `education_training` | Education and training records |
| `political_networks` | Political relationships and networks |
| `inferred_data` | Analytical and inferred information |

Additional supporting tables are used for administration, data imports, review workflows, and relationship management.

## Installation

### 1. Clone the Repository

```bash
git clone <YOUR_GITHUB_REPOSITORY_URL>

cd cpc-leadership
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Create the environment file:

```bash
cp .env.example .env
```

Update your `.env` configuration:

```env
APP_NAME="CPC Leadership"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpc_leadership
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Never commit actual database credentials to GitHub.

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Configure Database

Create a MySQL database:

```sql
CREATE DATABASE cpc_leadership;
```

Import the prepared database schema and data if you are setting up an existing project database.

For a new database with complete Laravel migrations:

```bash
php artisan migrate
```

**Important:** If you are importing an existing SQL database, verify the existing tables and migration history before running migrations. Always take a database backup before making schema changes.

### 6. Configure Storage

```bash
php artisan storage:link
```

For Linux/Apache deployments:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache

sudo chmod -R 775 storage bootstrap/cache
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit:

```text
http://127.0.0.1:8000
```

## Relationship Network Data

The project uses CSV datasets to populate relationship information and construct political network visualisations.

### Source CSV Files

```text
Relationships Table_14 November_nodes.csv
Relationships Table_14 November.csv
```

These datasets contain node and relationship information used by the network visualisation system.

Before importing, ensure that:

- Both CSV files are available in the expected import locations.
- The database contains the required relationship tables.
- CSV column names match the importer's expected structure.
- Existing records are backed up before bulk updates.

The network visualisation uses Cytoscape.js to display relationships between political figures and other entities.

## Public Website Routes

| Route | Description |
|---|---|
| `/` | Public homepage |
| `/leaders` | Leader directory |
| `/leaders/{leader}` | Individual leader profile |
| `/leaders/{leader}/network` | Leader relationship network |

## Administration Routes

| Route | Description |
|---|---|
| `/admin/login` | Administrator login |
| `/admin` | Administration dashboard |

Administrative pages require authentication and appropriate permissions.

## Production Deployment

The application is deployed on an AWS EC2 Ubuntu server.

### Deployment Directory

```bash
/var/www/cpc-leadership
```

### Production Environment

Update `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_DOMAIN_OR_SERVER_IP
```

### Install Production Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Set Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache

sudo chmod -R 775 storage bootstrap/cache
```

### Optimize Laravel

```bash
php artisan config:cache

php artisan route:cache

php artisan view:cache
```

### Apache Configuration

Configure the Apache virtual host to point to:

```text
/var/www/cpc-leadership/public
```

Do not configure the web root to point to the Laravel project root.

## Security

- Never commit `.env` files.
- Never expose database credentials.
- Keep `APP_DEBUG=false` in production.
- Restrict administrative access.
- Use strong administrator passwords.
- Store private imports outside the publicly accessible directory.
- Maintain regular database backups.
- Validate CSV data before importing.
- Test database changes in a staging environment before production deployment.

## Development Guidelines

- Follow Laravel MVC architecture.
- Keep database operations inside appropriate models and services.
- Use Laravel relationships wherever possible.
- Keep Blade templates focused on presentation.
- Validate and sanitise imported data.
- Maintain consistent database relationships.
- Preserve data integrity when updating historical records.

## Future Development

Potential improvements include:

- Advanced leader search and filtering.
- Expanded political network analysis.
- Timeline-based career visualisations.
- Improved data import and validation workflows.
- Advanced research and comparison tools.
- Enhanced SEO and structured metadata.
- API access for research datasets.

## License

Add the applicable project license and usage terms before making the repository publicly available.

---

**CPC Leadership**

A research platform for understanding Chinese Communist Party leadership, political careers, and institutional relationships.
