# TYPO3 Healthcheck Extension

[![TYPO3](https://img.shields.io/badge/TYPO3-v11%20%7C%20v12%20%7C%20v13-orange.svg)](https://typo3.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](LICENSE)

> A comprehensive health monitoring extension for TYPO3 CMS that provides automated system checks and monitoring capabilities.

## Table of Contents

- [What Does It Do?](#what-does-it-do)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Extension Configuration](#extension-configuration)
  - [TypoScript Configuration](#typoscript-configuration)
- [Probes](#probes)
  - [Available Probes](#available-probes)
  - [Creating Custom Probes](#creating-custom-probes)
- [Output Formats](#output-formats)
  - [HTML Output](#html-output)
  - [JSON Output](#json-output)
  - [Creating Custom Outputs](#creating-custom-outputs)
- [Accessing the Healthcheck](#accessing-the-healthcheck)
- [Pausing Probes](#pausing-probes)
- [HTTP Status Codes](#http-status-codes)
- [Credits](#credits)

---

## What Does It Do?

The **TYPO3 Healthcheck Extension** is a powerful monitoring tool designed to continuously assess the health and operational status of your TYPO3 installation. Think of it as a digital doctor for your website that regularly checks vital signs and reports any issues.

### Key Benefits

- **Proactive Monitoring**: Automatically detects issues before they impact your users
- **System Overview**: Provides a quick snapshot of your TYPO3 system's health status
- **Integration Ready**: Works seamlessly with monitoring tools like PRTG, Nagios, and other monitoring solutions
- **Customizable**: Easily extend with your own checks specific to your needs
- **Visual Feedback**: Beautiful HTML interface showing all check results at a glance
- **API Access**: JSON output for programmatic access and automated monitoring

The extension runs various diagnostic checks (called "probes") on critical system components like databases, caches, scheduled tasks, and search indices. Each probe reports success or failure, and if any probe fails, the entire healthcheck status reflects this, making it easy for monitoring systems to detect problems.

---

## Installation

Install the extension via Composer:

```bash
composer require worlddirect/healthcheck
```

After installation, activate the extension in the TYPO3 Extension Manager or via CLI:

```bash
php vendor/bin/typo3 extension:activate healthcheck
```

---

## Configuration

### Extension Configuration

Configure the extension through the TYPO3 Extension Manager or by editing your site's configuration. The following settings are available:

#### Security Settings

| Setting | Description | Default | Required |
|---------|-------------|---------|----------|
| `pathSegment` | The URL path segment used to access the healthcheck (e.g., "healthcheck") | `healthcheck` | Yes |
| `trustedHostsPattern` | Regular expression pattern defining which hosts can access the healthcheck. **Must be configured!** Use `.*` to allow all hosts or a specific pattern like `^(localhost\|monitoring\.example\.com)$` | _(empty)_ | **Yes** |
| `allowedIps` | Comma-separated list of IP addresses allowed to access the healthcheck. Use `*` to allow all IPs | `*` | No |

#### Display Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `logoImage` | Path to the logo image displayed in HTML output | `EXT:healthcheck/Resources/Public/Icons/healthreport.png` |
| `backgroundImage` | Path to the background image for HTML output | `EXT:healthcheck/Resources/Public/Images/background.png` |
| `enableBuildinfo` | Show build information (requires `buildinfo` extension) | `1` (enabled) |
| `enableAdditionalInfo` | Show additional information like IP address and timestamp | `1` (enabled) |
| `enableDebug` | Enable debug mode to show detailed error messages | `0` (disabled) |

#### Probe-Specific Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `schedulerMaxMinutesLate` | Maximum minutes a scheduler task can be late before being considered failed | `10` |
| `solrMaxErrorCount` | Maximum number of Solr indexing errors before probe fails | `50` |

### TypoScript Configuration

Additional settings can be configured via TypoScript:

```typoscript
plugin.tx_healthcheck {
    settings {
        # Override background image via TypoScript
        backgroundImage = EXT:your_extension/Resources/Public/Images/custom-background.png
    }
}
```

---

## Probes

Probes are the heart of the healthcheck system. Each probe checks a specific aspect of your TYPO3 installation and reports whether it's functioning correctly.

### Available Probes

#### Database Connection

**Class**: `DatabaseProbe`  
**Title**: "Database Connection"  
**Always Active**: Yes

Checks if the TYPO3 application can connect to all configured databases. This probe attempts a simple query on each database connection to verify accessibility.

**What it checks:**
- All database connections defined in your TYPO3 configuration
- Basic query execution capability
- Connection availability

**Fails when:**
- Any database connection cannot be established
- Query execution fails on any connection

---

#### Cache System

**Class**: `CacheProbe`  
**Title**: "Cache System"  
**Always Active**: Yes (except in Development context)

Verifies that all configured caches are writable and functioning correctly. This ensures your TYPO3 instance can properly store and retrieve cached data.

**What it checks:**
- All configured cache backends
- Write and read operations for each cache
- Cache backend availability

**Fails when:**
- Any cache cannot be written to
- Any cache backend is misconfigured
- Cache operations fail

**Note**: This probe is automatically disabled in Development context as development systems often have caching disabled.

---

#### Scheduler Tasks

**Class**: `SchedulerProbe`  
**Title**: "Scheduler Tasks"  
**Requires**: EXT:scheduler

Monitors TYPO3 scheduler tasks for failures and delays. This ensures your automated tasks are running on schedule.

**What it checks:**
- Failed scheduler tasks
- Tasks that are significantly delayed (configurable via `schedulerMaxMinutesLate`)
- Task execution status

**Fails when:**
- Any scheduler task has a failure status
- Tasks are running later than the configured maximum delay

**Configuration**: Set `schedulerMaxMinutesLate` in extension configuration to adjust delay tolerance.

---

#### Solr Core Connectivity

**Class**: `SolrCoreProbe`  
**Title**: "Solr Core Connectivity"  
**Requires**: EXT:solr

Tests connectivity to all configured Solr cores across all site languages. Ensures your search functionality is operational.

**What it checks:**
- Connection to each configured Solr core
- Solr server availability
- Core accessibility for each language

**Fails when:**
- Cannot connect to any configured Solr server
- Any Solr core is unreachable
- Solr ping request fails

**Configuration**: Reads Solr configuration from site configuration (host, port, path, core, scheme).

---

#### Solr Index Errors

**Class**: `SolrIndexErrorProbe`  
**Title**: "Solr Index Errors"  
**Requires**: EXT:solr

Monitors the Solr indexing queue for errors that prevent content from being indexed.

**What it checks:**
- Number of items in the Solr index queue with errors
- Error count against configured threshold

**Fails when:**
- Number of index errors exceeds `solrMaxErrorCount`

**Configuration**: Set `solrMaxErrorCount` in extension configuration to adjust error tolerance.

---

#### External Import

**Class**: `ExternalImportProbe`  
**Title**: "External Import"  
**Requires**: EXT:external_import

Checks the status of the latest External Import extension log entry to detect import failures.

**What it checks:**
- Latest import log entry status
- Import success/failure state

**Fails when:**
- Latest import log entry has a non-zero (error) status

---

### Creating Custom Probes

You can easily create your own probes to check specific aspects of your TYPO3 installation.

#### Step 1: Implement the ProbeInterface

Create a new PHP class that implements the `ProbeInterface`:

```php
<?php

namespace Vendor\YourExtension\Probe;

use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;

class CustomProbe extends ProbeBase implements ProbeInterface
{
    /**
     * Determine if this probe should run.
     * Use this to check prerequisites (e.g., if an extension is loaded).
     *
     * @return bool
     */
    public function useProbe(): bool
    {
        // Return true if the probe should run
        return true;
    }

    /**
     * Get the display title for this probe.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'My Custom Check';
    }

    /**
     * Execute the probe logic.
     *
     * @return void
     */
    public function run(): void
    {
        // Start timing the probe
        parent::start();

        // Your custom check logic here
        try {
            // Perform your checks
            $result = $this->performCustomCheck();

            if ($result) {
                // Add success message
                $this->result->addSuccessMessage('Custom check passed successfully');
            } else {
                // Add error message
                $this->result->addErrorMessage('Custom check failed: something went wrong');
            }
        } catch (\Exception $e) {
            // Handle exceptions
            $this->result->addErrorMessage('Custom check error: ' . $e->getMessage());
        }

        // Stop timing the probe
        parent::stop();
    }

    /**
     * Your custom check logic.
     *
     * @return bool
     */
    private function performCustomCheck(): bool
    {
        // Implement your check logic
        return true;
    }
}
```

#### Step 2: Register Your Probe

Register your custom probe in `Configuration/Services.yaml`:

```yaml
services:
  Vendor\YourExtension\Probe\CustomProbe:
    public: true
    tags:
      - name: 'healthcheck.probe'
```

#### Required Methods

Your probe **must** implement these three methods:

1. **`useProbe(): bool`** - Returns whether this probe should be executed. Use this to check dependencies like required extensions.

2. **`getTitle(): string`** - Returns a human-readable title for display in the HTML output.

3. **`run(): void`** - Contains the actual probe logic. Always call `parent::start()` at the beginning and `parent::stop()` at the end.

#### Important Guidelines

- **Always call `parent::start()` and `parent::stop()`** to properly track execution time
- **Always add at least one message** (success or error) to set the probe status
- Use `$this->result->addSuccessMessage()` for successful checks
- Use `$this->result->addErrorMessage()` for failures
- You can add multiple messages for detailed reporting
- Extend `ProbeBase` to inherit common functionality
- Use `$this->langService` to access translation services
- Access configuration via `$this->getExtensionConfiguration()`

---

## Output Formats

The healthcheck can return results in different formats depending on your needs.

### HTML Output

**Access**: `/healthcheck/html` or `/healthcheck/`  
**Content-Type**: `text/html`

A beautiful, responsive HTML interface built with Bootstrap 5 that provides:

- **Overall Status Indicator**: Clear visual indication (success/error) of system health
- **Probe Details**: Expandable accordion panels showing each probe's results
- **Color-Coded Status**: Green for success, red for errors
- **Execution Time**: Shows how long each probe took to execute
- **Pause/Play Controls**: Ability to temporarily disable probes (see [Pausing Probes](#pausing-probes))
- **System Information**: Optional display of build info, IP address, and timestamp
- **Responsive Design**: Works on desktop, tablet, and mobile devices

**Perfect for:**
- Manual system health checks
- Dashboard displays
- Quick visual overview
- Troubleshooting

---

### JSON Output

**Access**: `/healthcheck/json`  
**Content-Type**: `application/json`

Returns a structured JSON representation of all probe results.

**Example Response:**

```json
{
  "status": "SUCCESS",
  "duration": 0.453,
  "probes": [
    {
      "title": "Database Connection",
      "status": "SUCCESS",
      "duration": 0.023,
      "messages": [
        {
          "status": "SUCCESS",
          "message": "Database connection 'Default' is working"
        }
      ],
      "paused": false,
      "fqcn": "WorldDirect\\Healthcheck\\Probe\\DatabaseProbe"
    },
    {
      "title": "Cache System",
      "status": "SUCCESS",
      "duration": 0.145,
      "messages": [
        {
          "status": "SUCCESS",
          "message": "All caches are writable"
        }
      ],
      "paused": false,
      "fqcn": "WorldDirect\\Healthcheck\\Probe\\CacheProbe"
    }
  ]
}
```

**Perfect for:**
- Automated monitoring systems
- Programmatic access
- Integration with other tools
- Logging and analytics

---

### Creating Custom Outputs

You can create custom output formats for specialized needs.

#### Step 1: Implement the OutputInterface

```php
<?php

namespace Vendor\YourExtension\Output;

use WorldDirect\Healthcheck\Output\OutputBase;
use WorldDirect\Healthcheck\Output\OutputInterface;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;

class CustomOutput extends OutputBase implements OutputInterface
{
    /**
     * Generate the output content.
     *
     * @param HealthcheckResult $result
     * @return string
     */
    public function getContent(HealthcheckResult $result): string
    {
        // Generate your custom output
        // Access probes via: $result->getProbes()
        // Access overall status via: $result->getStatus()
        
        return 'Your custom formatted output';
    }

    /**
     * Return the content type for this output.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return 'text/plain'; // or 'application/xml', 'text/csv', etc.
    }
}
```

#### Step 2: Register Your Output

Register in `Configuration/Services.yaml`:

```yaml
services:
  Vendor\YourExtension\Output\CustomOutput:
    public: true
    tags:
      - name: 'healthcheck.output'
```

#### Step 3: Access Your Output

Access via: `/healthcheck/custom` (using the lowercase class name without "Output" suffix)

---

## Accessing the Healthcheck

The healthcheck is accessed through a special URL structure that uses TYPO3 middleware.

### URL Structure

```
https://your-domain.com/{pathSegment}/{output}/
```

**Components:**

1. **pathSegment**: The base path configured in extension settings (default: `healthcheck`)
2. **output**: The output format to use (optional, defaults to `html`)

### Examples

| URL | Description |
|-----|-------------|
| `https://example.com/healthcheck/` | HTML output (default) |
| `https://example.com/healthcheck/html/` | HTML output (explicit) |
| `https://example.com/healthcheck/json/` | JSON output |
| `https://example.com/mycheck/html/` | HTML output with custom pathSegment |

### Security

Before accessing the healthcheck, ensure you've configured:

1. **trustedHostsPattern**: Set to `.*` for all hosts or a specific pattern
2. **allowedIps**: Configure if you want to restrict by IP address

Without proper configuration, access will be denied with appropriate error messages.

---

## Pausing Probes

During maintenance or when dealing with known issues, you can temporarily pause individual probes to prevent false alerts.

### How Pausing Works

When a probe is paused:
- ✅ It still appears in the healthcheck output
- ✅ Its status is clearly marked as "PAUSED"
- ✅ It doesn't affect the overall healthcheck status
- ✅ Other probes continue to run normally
- ✅ No false alerts are sent to monitoring systems

### Using Pause Controls

#### In the HTML Interface

Each probe has pause/play buttons:

1. **Pause Button** (▶️ icon): Click to pause a failing probe
2. **Play Button** (⏸️ icon): Click to resume a paused probe

The interface provides immediate visual feedback:
- Paused probes show a "⏸️ PAUSED" indicator
- Active probes show a "▶️ ACTIVE" indicator

#### Via API

You can also pause/play probes programmatically:

**Pause a probe:**
```
GET /healthcheck-pause/?className=WorldDirect\Healthcheck\Probe\DatabaseProbe
```

**Resume a probe:**
```
GET /healthcheck-play/?className=WorldDirect\Healthcheck\Probe\DatabaseProbe
```

**Response:**
```json
{
  "success": true,
  "message": "Probe paused successfully"
}
```

### Use Cases

- **Scheduled Maintenance**: Pause relevant probes before maintenance windows
- **Known Issues**: Temporarily silence alerts for issues being worked on
- **External Dependencies**: Pause probes when external services are down
- **Gradual Rollout**: Pause probes during deployments to avoid false positives

### Technical Details

Paused probes are stored in the database table `tx_healthcheck_domain_model_probe_pause`. Each entry records:
- Probe class name (FQCN - Fully Qualified Class Name)
- Pause timestamp
- Automatic cleanup when probe is resumed

---

## HTTP Status Codes

The healthcheck uses HTTP status codes to indicate system health, making it easy for monitoring tools to detect issues.

| Status Code | Meaning | When Returned |
|-------------|---------|---------------|
| `200 OK` | System healthy | All probes passed successfully |
| `503 Service Unavailable` | System has issues | At least one probe failed |
| `403 Forbidden` | Access denied | IP or host not allowed |
| `404 Not Found` | Invalid output | Requested output format doesn't exist |

### Monitoring Integration

Most monitoring tools can check HTTP status codes:

**PRTG**: Use HTTP sensor checking for status code 200

**Nagios**: Use `check_http` with `--expect 200`

**Uptime Robot**: Configure HTTP(s) monitoring with keyword checking

**Custom Scripts**:
```bash
#!/bin/bash
response=$(curl -s -o /dev/null -w "%{http_code}" https://example.com/healthcheck/json/)
if [ $response -eq 200 ]; then
    echo "OK: System healthy"
    exit 0
else
    echo "CRITICAL: System unhealthy (Status: $response)"
    exit 2
fi
```

---

## Credits

### Development

Created by **Klaus Hörmann-Engl** (World-Direct)

### Inspiration

- **Axel Seemann** (aseemann) - [healthcheck.php Gist](https://gist.github.com/aseemann/42041fccb784cf472349a7af9748fe9b)
- **Georg Ringer** - [t3monitoring_client](https://github.com/georgringer/t3monitoring_client) extension

### Icons and Images

- **Success Icon**: [Tick icons by kliwir art - Flaticon](https://www.flaticon.com/free-icons/tick)
- **Error Icon**: [Error icons by Ilham Fitrotul Hayat - Flaticon](https://www.flaticon.com/free-icons/error)
- **Health Report Icon**: [Medical record icons by Freepik - Flaticon](https://www.flaticon.com/free-icons/medical-record)
- **Pause Icon**: [Pause icons by Kiranshastry - Flaticon](https://www.flaticon.com/free-icons/pause)
- **Play Icon**: [Play icons by Freepik - Flaticon](https://www.flaticon.com/free-icons/play)

---

## License

This extension is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, either version 2 of the License, or any later version.

For the full copyright and license information, please read the [LICENSE](LICENSE) file that was distributed with this source code.

---

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/world-direct-cms/healthcheck).

**Happy Monitoring! 🏥✨**
