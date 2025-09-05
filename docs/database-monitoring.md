# Database Monitoring System

This document describes the database monitoring and performance tracking system implemented in the GeoCasa Bohol application.

## Overview

The database monitoring system provides:
- Slow query detection and logging
- General query logging (optional)
- Performance metrics collection
- Database statistics monitoring
- Alert system for performance issues

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Database Monitoring Configuration
DB_MONITORING_ENABLED=true
DB_SLOW_QUERY_THRESHOLD=1000
DB_QUERY_LOGGING_ENABLED=false
DB_QUERY_LOG_CHANNEL=database
DB_QUERY_LOG_BINDINGS=true
DB_METRICS_ENABLED=true
DB_ALERTS_ENABLED=false
```

### Configuration Options

- `DB_MONITORING_ENABLED`: Enable/disable the entire monitoring system
- `DB_SLOW_QUERY_THRESHOLD`: Threshold in milliseconds for slow query detection (default: 1000ms)
- `DB_QUERY_LOGGING_ENABLED`: Enable logging of all database queries (use with caution in production)
- `DB_QUERY_LOG_CHANNEL`: Log channel for general query logging
- `DB_QUERY_LOG_BINDINGS`: Include query parameter bindings in logs
- `DB_METRICS_ENABLED`: Enable collection of performance metrics
- `DB_ALERTS_ENABLED`: Enable alert system for performance issues

## Log Files

The system creates separate log files for different types of monitoring:

### Slow Query Log
- **File**: `storage/logs/slow-queries-YYYY-MM-DD.log`
- **Purpose**: Records queries that exceed the configured threshold
- **Retention**: 30 days
- **Level**: Warning

### Database Query Log
- **File**: `storage/logs/database-YYYY-MM-DD.log`
- **Purpose**: Records all database queries (when enabled)
- **Retention**: 14 days
- **Level**: Debug/Info

### Alerts Log
- **File**: `storage/logs/alerts-YYYY-MM-DD.log`
- **Purpose**: Records critical performance alerts
- **Retention**: 90 days
- **Level**: Critical

## Monitoring Commands

### Database Monitor Command

Use the Artisan command to check current database performance:

```bash
# Show current database statistics
php artisan db:monitor

# Generate performance report from logs
php artisan db:monitor --report
```

### Command Output

The monitor command provides:
- Active connection count
- Active query count
- Top 10 largest tables by size
- Index usage statistics
- Slow query analysis (with --report flag)

## Performance Optimization Features

### Implemented Optimizations

1. **Database Indexes**
   - Fulltext search indexes on searchable columns
   - Composite indexes for complex queries
   - Foreign key indexes for relationships

2. **Query Optimization**
   - Eager loading to prevent N+1 queries
   - Query result caching for frequently accessed data
   - Optimized search queries

3. **Monitoring and Alerting**
   - Slow query detection
   - Performance metrics collection
   - Database statistics monitoring

### Key Indexes Added

#### Fulltext Search Indexes
- `properties`: title, description, address, municipality
- `users`: name, email
- `clients`: name, email, phone
- `inquiries`: message
- `transactions`: notes
- `seller_requests`: business_name, description
- `messages`: content

#### Composite Indexes
- `properties`: broker_id + status, status + created_at, municipality + type, total_price + status
- `inquiries`: property_id + status, client_id + status, status + created_at
- `transactions`: broker_id + status, property_id + status, status + created_at
- `clients`: broker_id + status, status + created_at
- `users`: role + created_at
- `conversations`: inquiry_id + updated_at
- `messages`: conversation_id + created_at, sender_id + created_at

## Best Practices

### Production Recommendations

1. **Slow Query Threshold**: Set to 500-1000ms for production
2. **Query Logging**: Keep disabled in production unless debugging
3. **Log Retention**: Monitor disk space and adjust retention periods
4. **Regular Monitoring**: Run `php artisan db:monitor` regularly
5. **Index Maintenance**: Monitor index usage and remove unused indexes

### Performance Monitoring

1. **Daily Checks**: Review slow query logs daily
2. **Weekly Reports**: Generate performance reports weekly
3. **Monthly Analysis**: Analyze table growth and index effectiveness
4. **Quarterly Review**: Review and optimize database schema

## Troubleshooting

### Common Issues

1. **High Slow Query Count**
   - Review slow query log for patterns
   - Check if indexes are being used
   - Consider query optimization

2. **Large Log Files**
   - Adjust log retention periods
   - Disable query logging if not needed
   - Implement log rotation

3. **Performance Degradation**
   - Check active connection count
   - Review table sizes and growth
   - Analyze index usage statistics

### Log Analysis

Slow query logs include:
- SQL query with bindings
- Execution time
- Request URL and user context
- Memory usage information
- Database connection details

## Integration with Existing Systems

The monitoring system integrates with:
- Laravel's logging system
- Existing error monitoring (Sentry)
- Backup system notifications
- Application performance monitoring

## Future Enhancements

- Integration with external monitoring tools (New Relic, DataDog)
- Real-time performance dashboards
- Automated query optimization suggestions
- Database health scoring system
- Integration with notification systems (Slack, email)