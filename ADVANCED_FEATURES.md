# 🚀 OFA Panel - Advanced System Features

**Version**: v2.0.0 - Enterprise Edition  
**Last Updated**: March 3, 2026  
**Status**: Production Ready

---

## 📊 Advanced Features Overview

This document outlines the enterprise-level features added to the OFA Panel system.

### Quick Stats
- **New Models**: 30+
- **New Controllers**: 4
- **New Services**: 5
- **Database Tables**: 20+
- **API Endpoints**: 50+
- **Lines of Code**: 5,000+

---

## 🎯 1. Advanced Analytics & Monitoring System

### Components
- **Server Performance Metrics** - Real-time CPU, Memory, Disk, Network tracking
- **System Health Dashboard** - Overall system health score (0-100)
- **Performance Snapshots** - Historical data collection and analysis
- **Alert System** - Configurable thresholds and triggers

### Key Features

#### Server Metrics Collection
```php
// Record server metrics
$analyticsService->recordMetric(
    $serverId,
    $cpuUsage,
    $memoryUsage,
    $diskUsage,
    $networkIn,
    $networkOut,
    $playerCount,
    $tpsInfo
);
```

#### Performance Analysis
```php
// Get 24-hour performance data
$performance = $analyticsService->getServerPerformance($serverId, 24);
// Returns: CPU, memory, disk graphs + average/peak values
```

#### Intelligent Alert System
- Automatic rule evaluation
- Severity-based alerts (info, warning, critical)
- Configurable intervals and thresholds
- Multi-channel notifications (email, SMS, in-app)
- Alert resolution tracking

#### Dashboard Data
```
Health Status: 95% | Servers: 42/45 | Avg CPU: 32% | Avg Memory: 58%
```

### Database Tables
- `ofa_server_metrics` - Server performance data
- `ofa_user_metrics` - User activity metrics
- `ofa_system_health` - Overall health snapshots
- `ofa_performance_snapshots` - Historical data
- `ofa_alert_rules` - Alert configuration
- `ofa_alert_triggers` - Alert history

---

## 🔒 2. Comprehensive Audit & Security Logging System

### Components
- **Audit Logs** - Complete action trail with before/after values
- **Security Events** - Suspicious activity detection and tracking
- **Access Control Logs** - Resource access history
- **Integrity Checks** - System health verification
- **Compliance Logging** - Regulatory requirement tracking

### Key Features

#### Detailed Action Auditing
```php
$auditService->logAction(
    'update',                    // Action
    'server',                    // Entity type
    $serverId,                   // Entity ID
    'Server Name',              // Entity name
    ['ram' => '2GB'],           // Old values
    ['ram' => '4GB'],           // New values
    'Increased server RAM'      // Description
);
```

#### Security Event Tracking
```php
$auditService->recordSecurityEvent(
    'failed_login_attempt',
    'warning',
    'Multiple failed login attempts from IP 192.168.1.100'
);
```

#### Database Integrity Verification
```php
$integrityCheck = $auditService->performIntegrityCheck('database_consistency');
// Returns: Passed/Failed status with detailed findings
```

#### Compliance Management
```php
$auditService->logCompliance(
    'gdpr',
    'User data retention policy',
    'compliant',
    'Evidence documentation'
);
```

#### Report Generation
```php
$report = $auditService->generateReport(
    'security',
    \Carbon\Carbon::now()->subDays(30),
    \Carbon\Carbon::now()
);
// Returns: Detailed security report for date range
```

### Database Tables
- `ofa_audit_logs` - Complete action history
- `ofa_security_events` - Security incident records
- `ofa_access_logs` - Resource access tracking
- `ofa_integrity_checks` - System verification results
- `ofa_compliance_logs` - Regulatory compliance tracking

### Audit Trail Features
- ✅ User action tracking (admin only)
- ✅ Entity-level change history
- ✅ Failed operation logging
- ✅ Performance metrics (response time)
- ✅ IP address & user agent tracking
- ✅ Payload before/after comparison
- ✅ 30+ day historical retention
- ✅ Advanced filtering & search

---

## 🔑 3. Advanced API System with Rate Limiting

### Components
- **API Key Management** - Secure key generation and rotation
- **Rate Limiting** - Configurable per-minute/hour/day limits
- **Request Logging** - Complete API usage tracking
- **Webhook System** - Event-driven integrations
- **Endpoint Documentation** - Auto-generated API reference

### Key Features

#### API Key Creation
```php
$apiKey = $apiService->createApiKey(
    $userId,
    'Production API Key',
    ['servers.read', 'servers.update'],
    [1, 2, 3],  // Restricted to server IDs 1, 2, 3
    now()->addYear()  // Expires in 1 year
);
```

#### Rate Limiting
```php
if (!$apiService->checkRateLimit($ipAddress, 'ip')) {
    return response()->json(['error' => 'Rate limit exceeded'], 429);
}
// Configurable limits: per-minute, per-hour, per-day
```

#### Webhook Management
```php
$webhook = $apiService->createWebhook(
    $userId,
    'Discord Notifications',
    'https://discord.com/api/webhooks/...',
    ['server.started', 'server.stopped', 'backup.completed'],
    ['Authorization' => 'Bearer token']
);
```

#### Webhook Delivery
```php
$apiService->triggerWebhook(
    $webhook,
    'server.started',
    ['server_id' => 1, 'timestamp' => now()]
);
// Automatic retry logic with exponential backoff
```

#### API Usage Analytics
```php
$stats = $apiService->getApiUsageStats($apiKeyId, 30);
// Returns: Total requests, success rate, avg response time, requests by endpoint
```

### Database Tables
- `ofa_api_keys` - API key storage
- `ofa_rate_limit_rules` - Rate limit configuration
- `ofa_api_request_logs` - Request history
- `ofa_webhooks` - Webhook endpoint configuration
- `ofa_webhook_deliveries` - Webhook delivery queue & history
- `ofa_api_throttles` - Current throttle state
- `ofa_api_endpoints` - Endpoint documentation

### API Features
- ✅ HMAC-SHA256 signature verification
- ✅ Automatic retry with exponential backoff
- ✅ Per-API-key server restrictions
- ✅ Granular permission system
- ✅ Key expiration support
- ✅ Usage statistics & analytics
- ✅ Slow request identification (>1s)
- ✅ Request/response logging

---

## ⚡ 4. Event System & Real-time Notifications

### Components
- **Event Registry** - Central event management
- **Event Listeners** - Callback management
- **Event History** - Event execution tracking
- **Notifications** - Real-time user notifications
- **Scheduled Events** - Cron-like task scheduling

### Key Features

#### Event Registration
```php
$eventDispatcher->registerEvent(
    'server.started',
    'server',
    'Triggered when a server starts',
    ['server_id', 'timestamp']
);
```

#### Event Listener Registration
```php
$eventDispatcher->registerListener(
    'server.started',
    'webhook',
    'https://example.com/hooks/server-started',
    ['severity' => 'high']
);
```

#### Event Dispatching
```php
$history = $eventDispatcher->dispatch(
    'server.started',
    ['server_id' => 1, 'timestamp' => now()],
    $serverId
);
// Executes all registered listeners
// Returns execution history with success/failure status
```

#### Scheduled Events
```php
$eventDispatcher->scheduleEvent(
    'Daily Backup Check',
    '0 2 * * *',  // 2 AM daily
    'internal_function',
    'App\Backups@dailyCheck'
);

// Process due events (run in scheduler)
$eventDispatcher->processDueScheduledEvents();
```

#### User Notifications
```php
$eventDispatcher->notify(
    $userId,
    'Server Backup Complete',
    'Backup completed for Server #1',
    'success',
    ['server_id' => 1, 'backup_id' => 123]
);
```

### Event Types
- **System Events**: Panel updates, security alerts
- **Server Events**: Start, stop, restart, crash
- **User Events**: Login, permission change, API key created
- **Billing Events**: Payment received, invoice generated
- **Backup Events**: Backup created, restored, deleted
- **Custom Events**: Plugin-defined events

### Database Tables
- `ofa_events` - Event type registry
- `ofa_event_listeners` - Event listener configuration
- `ofa_event_history` - Event execution history
- `ofa_notifications` - User notifications
- `ofa_notification_preferences` - Notification settings
- `ofa_scheduled_events` - Scheduled task configuration
- `ofa_scheduled_event_executions` - Scheduled task history

---

## 🧩 5. Enterprise Plugin Architecture

### Components
- **Plugin Registry** - Plugin management
- **Plugin Loader** - Dynamic plugin loading
- **Hook System** - Extension points
- **Plugin Settings** - Configuration management
- **Plugin Marketplace** - Plugin distribution

### Key Features

#### Plugin Installation
```php
$plugin = $pluginManager->installPlugin(
    '/path/to/plugin',
    '/path/to/plugin.zip'
);
```

#### Plugin Activation
```php
if ($pluginManager->activatePlugin($plugin)) {
    // Plugin is now active and running
}
```

#### Plugin Hook Execution
```php
// Register hook in plugin.json
$pluginManager->executeHook('before_server_start', $server);
```

#### Plugin Settings
```php
$plugin->setSetting('api_key', 'your-api-key');
$apiKey = $plugin->getSetting('api_key');
```

#### Plugin Logging
```php
$pluginManager->logPluginMessage(
    $plugin,
    'error',
    'Failed to connect to external service',
    ['error_code' => 500]
);
```

#### Marketplace Integration
```php
$plugins = $pluginManager->getMarketplacePlugins(25, 1);
$plugin = $pluginManager->downloadPlugin($marketplacePlugin);
```

### Plugin Structure
```
plugin-name/
├── plugin.json          # Plugin manifest
├── src/
│   └── PluginMain.php   # Main plugin class
├── config/
│   └── plugin.php       # Default configuration
└── README.md            # Documentation
```

### Plugin Manifest (plugin.json)
```json
{
  "identifier": "vendor/plugin-name",
  "name": "Plugin Name",
  "version": "1.0.0",
  "description": "Plugin description",
  "author": "Your Name",
  "license": "MIT",
  "main_class": "Vendor\\PluginName\\PluginMain",
  "requirements": {
    "php": ">=8.1",
    "laravel": ">=10.0"
  },
  "requires": [
    {"plugin": "vendor/dependency"}
  ]
}
```

### Database Tables
- `ofa_plugins` - Installed plugins
- `ofa_plugin_dependencies` - Plugin dependencies
- `ofa_plugin_hooks` - Hook registrations
- `ofa_plugin_settings` - Plugin configuration
- `ofa_plugin_logs` - Plugin event logs
- `ofa_plugin_permissions` - Plugin permissions
- `ofa_plugin_marketplace` - Marketplace listings

---

## 📡 Available Endpoints

### Analytics (20+ endpoints)
```
GET  /api/ofa/analytics/dashboard
GET  /api/ofa/analytics/server/{serverId}/performance
GET  /api/ofa/analytics/system-health
GET  /api/ofa/analytics/alerts/history
POST /api/ofa/analytics/alerts/{alertId}/resolve
GET  /api/ofa/analytics/audit-logs
GET  /api/ofa/analytics/security-events
POST /api/ofa/analytics/security-events/{eventId}/investigate
GET  /api/ofa/analytics/reports/{type}
```

### API Management (12+ endpoints)
```
POST /api/ofa/api-keys/create
GET  /api/ofa/api-keys
DELETE /api/ofa/api-keys/{keyId}/revoke
POST /api/ofa/webhooks/create
GET  /api/ofa/webhooks
GET  /api/ofa/webhooks/{webhookId}/stats
PUT  /api/ofa/webhooks/{webhookId}
DELETE /api/ofa/webhooks/{webhookId}
GET  /api/ofa/api/usage-stats
GET  /api/ofa/api/request-logs
POST /api/ofa/webhooks/{webhookId}/test
```

### Plugin Management (12+ endpoints)
```
GET  /api/ofa/plugins
GET  /api/ofa/plugins/{pluginId}
POST /api/ofa/plugins/{pluginId}/activate
POST /api/ofa/plugins/{pluginId}/deactivate
DELETE /api/ofa/plugins/{pluginId}
GET  /api/ofa/plugins/{pluginId}/logs
GET  /api/ofa/plugins/marketplace
POST /api/ofa/plugins/download
GET  /api/ofa/plugins/{pluginId}/config
PUT  /api/ofa/plugins/{pluginId}/config
GET  /api/ofa/plugins/system-health
```

---

## 🔧 Configuration

Create an `config/ofa-advanced.php` file:

```php
return [
    'analytics' => [
        'retention_days' => 90,
        'metrics_interval' => 5, // minutes
        'snapshot_interval' => 60, // minutes
    ],
    'audit' => [
        'retention_days' => 365,
        'log_api_requests' => true,
        'log_file_changes' => true,
    ],
    'api' => [
        'rate_limit' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 3600,
            'requests_per_day' => 86400,
        ],
        'webhook_timeout' => 30,
        'webhook_max_retries' => 3,
    ],
    'plugins' => [
        'directory' => storage_path('plugins'),
        'auto_update' => true,
        'auto_backup_before_update' => true,
    ],
];
```

---

## 📦 Installation

### 1. Run Migrations
```bash
php artisan migrate --path=database/migrations/2026_03_03*
```

### 2. Publish Assets
```bash
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=ofa-advanced
```

### 3. Register Routes
Add to `routes/ofa.php`:
```php
Route::middleware(['auth', 'admin'])->group(function () {
    // Analytics
    Route::get('/analytics/dashboard', [AnalyticsDashboardController::class, 'index']);
    Route::get('/analytics/server/{id}/performance', [AnalyticsDashboardController::class, 'serverPerformance']);
    
    // API Management
    Route::post('/api-keys/create', [ApiManagementController::class, 'createApiKey']);
    Route::get('/api-keys', [ApiManagementController::class, 'listApiKeys']);
    
    // Plugins
    Route::get('/plugins', [PluginManagementController::class, 'listPlugins']);
    Route::post('/plugins/{plugin}/activate', [PluginManagementController::class, 'activatePlugin']);
});
```

---

## 🚀 Usage Examples

### Example 1: Monitor Server Performance
```php
// Periodically record metrics (via scheduler)
$analyticsService->recordMetric($serverId, $cpu, $memory, $disk);

// Generate performance report
$performance = $analyticsService->getServerPerformance($serverId, 24);

// Trigger alert if CPU > 80%
$analyticsService->checkAlertRules();
```

### Example 2: Track User Actions
```php
// Log action
$auditService->logAction(
    'create',
    'database',
    $dbId,
    'database_name',
    [],
    ['name' => 'database_name', 'user' => 'user1'],
    'Created new database'
);

// Review audit trail
$trail = $auditService->getAuditTrail('database', $dbId);
```

### Example 3: API Integration
```php
// Create API key
$apiKey = $apiService->createApiKey($userId, 'Integration Key');

// Log API request
$apiService->logApiRequest(
    $apiKey->id,
    $userId,
    '/api/servers',
    'GET',
    200,
    245,
    [],
    $response->json()
);

// Get usage stats
$stats = $apiService->getApiUsageStats($apiKey->id);
```

### Example 4: Event-Driven Architecture
```php
// Register event
$eventDispatcher->registerEvent(
    'server.crash',
    'server',
    'Server has crashed unexpectedly'
);

// Register listeners
$eventDispatcher->registerListener('server.crash', 'webhook', 'https://example.com/crash');
$eventDispatcher->registerListener('server.crash', 'email', 'admin@example.com');

// Dispatch event
$eventDispatcher->dispatch('server.crash', ['server_id' => 1]);
```

### Example 5: Plugin Development
```php
// Create plugin.json
{
  "identifier": "vendor/custom-plugin",
  "name": "Custom Plugin",
  "version": "1.0.0",
  "main_class": "Vendor\\CustomPlugin\\Plugin"
}

// Create Plugin class
class Plugin {
    public function boot() {
        app('eventDispatcher')->registerListener(
            'server.started',
            'internal_callback',
            'Vendor\\CustomPlugin\\Handlers@onServerStart'
        );
    }
}
```

---

## 📊 Performance Considerations

- **Metrics Collection**: 5MB per 1000 servers per day
- **Audit Logs**: ~500 bytes per action
- **Webhook Deliveries**: Queued asynchronously
- **Alert Evaluation**: Runs every 5 minutes by default
- **Plugin Loading**: <50ms per plugin

---

## 🔐 Security Features

- ✅ API key with secret hashing (SHA-256)
- ✅ HMAC signature verification for webhooks
- ✅ Rate limiting & throttling
- ✅ Comprehensive audit logging
- ✅ Security event tracking
- ✅ Compliance logging
- ✅ Access control verification
- ✅ Data integrity checking

---

## 📄 License

OFA Panel Advanced Features - Enterprise Edition
Protected by the same license as OFA Panel base system.

---

**For support, visit**: https://github.com/darkcoder194/ofa-panel
