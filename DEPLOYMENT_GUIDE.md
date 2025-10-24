# Inquiry System Enhancement Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the enhanced inquiry system with all improvements, monitoring, and testing capabilities.

## Pre-Deployment Requirements

### 1. System Requirements

-   PHP 8.1+
-   Laravel 10+
-   MySQL 8.0+ or PostgreSQL 13+
-   Redis (for caching and queues)
-   Node.js 18+ (for frontend assets)

### 2. Dependencies Check

```bash
# Check PHP version
php --version

# Check Composer
composer --version

# Check Node.js
node --version

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Deployment Steps

### Step 1: Backup Current System

```bash
# Backup database
php artisan backup:run --only-db

# Backup current codebase
git stash push -m "Pre-deployment backup"

# Create deployment branch
git checkout -b inquiry-system-enhancement
```

### Step 2: Install Dependencies

```bash
# Update Composer dependencies
composer install --optimize-autoloader --no-dev

# Update Node dependencies
npm install
npm run build
```

### Step 3: Database Migration

```bash
# Run new migrations
php artisan migrate

# Verify migration status
php artisan migrate:status

# Seed test data (optional, for development)
php artisan db:seed --class=TestInquirySeeder
```

### Step 4: Configuration Updates

#### 4.1 Update Environment Variables

Add to your `.env` file:

```env
# Inquiry System Configuration
INQUIRY_DUPLICATE_PREVENTION_ENABLED=true
INQUIRY_BUSINESS_HOURS_START=09:00
INQUIRY_BUSINESS_HOURS_END=18:00
INQUIRY_RATE_LIMIT_PER_IP=10
INQUIRY_RATE_LIMIT_PER_EMAIL=5
INQUIRY_SIMILARITY_THRESHOLD=0.8

# Monitoring Configuration
INQUIRY_MONITORING_ENABLED=true
INQUIRY_HEALTH_CHECK_ENABLED=true

# Notification Configuration
INQUIRY_NOTIFICATIONS_ENABLED=true
INQUIRY_NOTIFICATION_CHANNELS=mail,database,broadcast
```

#### 4.2 Update Logging Configuration

The logging configuration has been automatically updated in `config/logging.php` with new channels:

-   `inquiry` - Dedicated inquiry logging
-   `performance` - Performance metrics
-   `security` - Security events
-   `audit` - Audit trail

### Step 5: Queue Configuration

```bash
# Configure queue workers
php artisan queue:table
php artisan migrate

# Start queue workers (use supervisor in production)
php artisan queue:work --queue=default,notifications --tries=3
```

### Step 6: Cache and Optimization

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 7: Service Registration

The new services are automatically registered through Laravel's service container. Verify registration:

```bash
php artisan tinker
>>> app(App\Services\BrokerAssignmentService::class);
>>> app(App\Services\DuplicatePreventionService::class);
>>> app(App\Services\InquiryMonitoringService::class);
```

## Post-Deployment Verification

### Step 1: Health Check

```bash
# Run comprehensive health check
php artisan inquiry:health-check --verbose --alert

# Check specific components
php artisan inquiry:health-check --component=inquiry_processing
php artisan inquiry:health-check --component=broker_assignment
php artisan inquiry:health-check --component=notifications
```

### Step 2: Test Inquiry Creation

```bash
# Test inquiry creation via API
curl -X POST http://your-domain.com/api/inquiries \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "phone": "+1234567890",
    "message": "I am interested in this property."
  }'
```

### Step 3: Verify Monitoring

```bash
# Generate initial metrics
php artisan inquiry:generate-metrics --days=1

# Check log files
tail -f storage/logs/inquiry.log
tail -f storage/logs/performance.log
```

### Step 4: Run Test Suite

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test tests/Feature/InquiryWorkflowTest.php
php artisan test tests/Unit/Services/BrokerAssignmentServiceTest.php
php artisan test tests/Unit/Services/DuplicatePreventionServiceTest.php
```

## Monitoring Setup

### Step 1: Schedule Commands

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generate daily metrics
    $schedule->command('inquiry:generate-metrics')
             ->dailyAt('01:00')
             ->withoutOverlapping();

    // Health check every 15 minutes
    $schedule->command('inquiry:health-check --alert')
             ->everyFifteenMinutes()
             ->withoutOverlapping();
}
```

### Step 2: Queue Monitoring

Set up queue monitoring with Supervisor:

```ini
[program:inquiry-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

### Step 3: Log Rotation

Configure log rotation in `/etc/logrotate.d/laravel`:

```
/path/to/your/app/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    postrotate
        /usr/bin/supervisorctl restart inquiry-queue-worker:*
    endscript
}
```

## Performance Optimization

### Step 1: Database Optimization

```sql
-- Add indexes for better performance (already included in migrations)
-- Verify indexes are created
SHOW INDEX FROM inquiries;
SHOW INDEX FROM inquiry_metrics;
SHOW INDEX FROM broker_performance_metrics;
```

### Step 2: Redis Configuration

```bash
# Configure Redis for caching and queues
redis-cli config set maxmemory 256mb
redis-cli config set maxmemory-policy allkeys-lru
```

### Step 3: PHP-FPM Optimization

Update `php-fpm.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

## Security Configuration

### Step 1: File Permissions

```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

### Step 2: Environment Security

```bash
# Secure .env file
chmod 600 .env
chown www-data:www-data .env
```

### Step 3: Rate Limiting

Configure rate limiting in `config/app.php` or use middleware for API endpoints.

## Troubleshooting

### Common Issues

#### 1. Migration Errors

```bash
# Check migration status
php artisan migrate:status

# Rollback if needed
php artisan migrate:rollback --step=1

# Fresh migration (development only)
php artisan migrate:fresh --seed
```

#### 2. Queue Issues

```bash
# Clear failed jobs
php artisan queue:flush

# Restart queue workers
php artisan queue:restart

# Check queue status
php artisan queue:monitor
```

#### 3. Permission Issues

```bash
# Fix storage permissions
php artisan storage:link
chmod -R 775 storage/
```

#### 4. Service Resolution Issues

```bash
# Clear service cache
php artisan clear-compiled
php artisan optimize:clear
```

### Debugging Commands

```bash
# Enable debug mode (development only)
php artisan down
# Set APP_DEBUG=true in .env
php artisan up

# Check logs
tail -f storage/logs/laravel.log
tail -f storage/logs/inquiry.log

# Database queries debugging
# Add DB::enableQueryLog() and DB::getQueryLog() in your code
```

## Rollback Procedure

If issues occur, follow this rollback procedure:

### Step 1: Immediate Rollback

```bash
# Put application in maintenance mode
php artisan down

# Restore database backup
# (Use your backup restoration procedure)

# Restore codebase
git stash pop
# or
git checkout previous-stable-branch
```

### Step 2: Verify Rollback

```bash
# Check application status
php artisan up
php artisan health:check

# Verify functionality
# Test critical inquiry creation flow
```

## Maintenance

### Daily Tasks

-   Monitor system health via `inquiry:health-check`
-   Review error logs
-   Check queue status

### Weekly Tasks

-   Generate and review performance metrics
-   Analyze duplicate prevention effectiveness
-   Review broker assignment distribution

### Monthly Tasks

-   Database maintenance and optimization
-   Log cleanup and archival
-   Performance tuning based on metrics

## Support and Documentation

### Key Files

-   **Service Classes**: `app/Services/`
-   **Tests**: `tests/Feature/` and `tests/Unit/`
-   **Migrations**: `database/migrations/`
-   **Commands**: `app/Console/Commands/`
-   **Configuration**: `config/logging.php`

### Monitoring Dashboards

-   System health: `php artisan inquiry:health-check --verbose`
-   Performance metrics: Review `inquiry_metrics` table
-   Error tracking: Monitor `storage/logs/inquiry.log`

### Contact Information

-   **Technical Lead**: [Your Name]
-   **DevOps Team**: [Team Contact]
-   **Emergency Contact**: [Emergency Number]

---

**Deployment Status**: Ready for Production  
**Last Updated**: [Current Date]  
**Version**: 2.0.0  
**Compatibility**: Laravel 10+, PHP 8.1+
