<!-- omit in toc -->
# TYPO3 extension: "healthcheck"

- [What does it do?](#what-does-it-do)
- [Configuration](#configuration)
- [Probes](#probes)
  - [CacheProbe](#cacheprobe)
  - [DatabaseProbe](#databaseprobe)
  - [SchedulerProbe](#schedulerprobe)
- [Outputs](#outputs)
  - [HtmlOutput](#htmloutput)
- [Customization](#customization)
  - [Add custom probes](#add-custom-probes)
  - [Add custom outputs](#add-custom-outputs)
- [Inspiration](#inspiration)
- [Used resources](#used-resources)

## What does it do?
The TYPO3 extension **"healthcheck"** provides various output possibilities to schow a healthcheck for the TYPO3 project. It checks various extensible probes for their health and summarizes these into a overall health status. These informations can be used by a monitoring software tool like **"PRTG"** to monitor the TYPO3 project.
## Configuration
TODO

## Probes

The extension provides these probes out of the box:
- **CacheProbe:** Checks if all cache configurations are writeable
- **DatabaseProbe:** Checks if the access to the database works
- **SchedulerProbe:** Check if there are any scheduled tasks, which have a failure

### CacheProbe
TODO
### DatabaseProbe
TODO

### SchedulerProbe
TODO

## Outputs

The extension provides these output formats out of the box:
- **HtmlOutput:** Renders a HTML page using Bootstrap 5 to display the HealthcheckResult. Showing a overall Success or Error.
- **JsonOutput:** Simple JSON output which displays the HealthcheckResult as JSON.

### HtmlOutput

The **HtmlOutput**

- Uses a configured **"backendLogo"** in the HTML output of the Healthcheck. It this is not set, the extension uses the TYPO3 backend logo.

## Customization

This extension is customizable in a way, that allows the developer to add custom **probes** and **outputs**.

### Add custom probes
TODO

### Add custom outputs
TODO


## Inspiration

This extension was inspired by the GitHub Gist of **Axel Seemann** (aseemann). For details on the Gist see here: [healthcheck.php](https://gist.github.com/aseemann/42041fccb784cf472349a7af9748fe9b). Also did I take some ideas from the extension **t3monitoring_client** from **Ringer Georg**. For details on this extension see here: [t3monitoring_client](https://github.com/georgringer/t3monitoring_client).
## Used resources

The credit for the images goes to:
- success.png: <a href="https://www.flaticon.com/free-icons/tick" title="tick icons">Tick icons created by kliwir art - Flaticon</a>
- danger.png: <a href="https://www.flaticon.com/free-icons/error" title="error icons">Error icons created by Ilham Fitrotul Hayat - Flaticon</a>
- health-report.png: <a href="https://www.flaticon.com/free-icons/medical-record" title="medical record icons">Medical record icons created by Freepik - Flaticon</a>