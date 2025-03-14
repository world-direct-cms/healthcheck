<!-- omit in toc -->
# TYPO3 extension: "healthcheck"

<!-- omit in toc -->
## Content
- [What does it do?](#what-does-it-do)
- [Installation](#installation)
- [Versions and branches](#versions-and-branches)
- [Accessing the healthcheck](#accessing-the-healthcheck)
- [Returned HTTP status code](#returned-http-status-code)
- [Configuration](#configuration)
  - [Extension configuration](#extension-configuration)
  - [TypoScript configuration](#typoscript-configuration)
- [Probes](#probes)
  - [CacheProbe](#cacheprobe)
  - [DatabaseProbe](#databaseprobe)
  - [ExternalImportProbe](#externalimportprobe)
  - [SchedulerProbe](#schedulerprobe)
- [Outputs](#outputs)
  - [HtmlOutput](#htmloutput)
- [Customization](#customization)
  - [Add custom probes](#add-custom-probes)
  - [Add custom outputs](#add-custom-outputs)
- [Pausing a probe](#pausing-a-probe)
- [Inspiration](#inspiration)
- [Credits](#credits)

## What does it do?
The TYPO3 extension **"healthcheck"** provides outputs, each showing healthcheck information for the current TYPO3 installation. The extension runs various probes which report back a status depending on success or error. This results in an overall Healthcheck result. The healthcheck output may be used by a variety of monitoring software like **PRTG**. 

<img src="Resources/Public/Images/documentation/healthcheck-screenshot.png" width="600" />

## Installation
The extension is installable using Composer:
```bash
composer req worlddirect/healthcheck
```

## Versions and branches
The `master` branch contains the latest version comnpatible with TYPO3 V11 and TYPO3 V12. There is a `feature-v10` branch, which contains a backport of the extension to be comnpatible with TYPO3 V10. It misses some configuration options and new features of PHP (like Enums). It was made quick and dirty, just to have a healthcheck for older TYPO3 V10 installations as well. There will not be any updates to this branch, after upgrading all projects to v11 or v12 or ...

The TYPO3 V10 version will be a "0.xx.xx". The current versions for TYPO3 V11 and V12 have the version numbering "1.xx.xx".

## Accessing the healthcheck
The **Healthcheck** uses a Middleware to render the output. In order for the Middleware to know that a possible healthcheck needs to be rendered, we use the extension configuration settings **pathSegment** (as well as the output type).

The healthcheck can be accessed by a "path" using 3 parts. Here is an example URL:
https://www.mustermann.de/healthcheck/lkjl23wsdkjjlskdj/html/.
- **1. part:** The first of the "path" is the *"pathSegment"*. In the example this is **"healthcheck"**.
- **2. part:** The third and final part of the "path" contains the name of the desired Output. In this case it uses "html" which represents the **"HtmlOutput"**. If this part is omitted, the default "html" is used.

## Returned HTTP status code
The returned response status code depends on the status of the HealthcheckResult object. If the status equals an **"ERROR"** a http status code `503` is returned. If the status is a **"SUCCESS"** a code of `200` is returned. This makes it possible to check for the http status code in order to determine if there is a problem with the healthcheck. No need to interpret the output. There are tools, as mentioned before which can perform checks for a specific HTTP status code. Or you build your own litte script to do so. :smiley:

## Configuration
There is an extension configuration as well as a TypoScript settings configuration.

### Extension configuration
The extension configuration holds various settings, which need to be set in order for the healthcheck to work. Also showing debugging informations can be enabled.

**pathSegment:** This setting sets the 1. part of the "path" used to access the healthcheck. It's default setting is **"healthcheck"** and may be adapted by the user.

**allowedIps:** It is possible to limit the allowedIps which may view the healthcheck. Default value is **"*"** (every IP address).

**enableDebug:** This setting allows to output some more debugging information. If you enable the **"enableDebug"** setting you will at least see a short message about the current error. Default value is **"off"** (0).

### TypoScript configuration
The TypoScript configuration is used for any other settings. Currently there is only one setting for the HTML output.

**backgroundImage:** This setting is relevant for the HTML output and contains the background image to use for the HtmlOutput class instance.

## Probes

In order to check for certain functionality, data or states we use **Probes** to do so. These probes have a **"run"** method which checks certain parts, and fills the probe's result with *success* or *error* messages. If there is at least 1 *error* message the overall status of the *HealthcheckResult* is *ERROR*. 

> All probes need to have the status "SUCCESS" for the overall HealthcheckResult Status to be "SUCCESS"!

The extension already provides three probes out of the box:
- **CacheProbe:** Checks if all cache configurations are writeable
- **DatabaseProbe:** Checks if the access to the database works
- **ExternalImportProbe:** Checks the ExternalImport extension logs last entry for a failure
- **SchedulerProbe:** Check if there are any scheduled tasks, which have a failure

### CacheProbe
TODO: What is the CacheProbe?

### DatabaseProbe
TODO: What is the DatabaseProbe?

### ExternalImportProbe
TODO: What is ths ExternalImportProbe

### SchedulerProbe
TODO: What does the SchedulerProbe do?

## Outputs
TODO: What is a output?

The extension provides these output formats out of the box:
- **HtmlOutput:** Renders a HTML page using Bootstrap 5 to display the HealthcheckResult. Showing a overall Success or Error.
- **JsonOutput:** Simple JSON output which displays the HealthcheckResult as JSON.

### HtmlOutput
TODO: How does the HtmlOutput work?

## Customization

This extension is customizable in a way, that allows the developer to add new **probes** as well as **outputs**.

### Add custom probes
TODO: Explain Interface
TODO: Explain BaseClass

### Add custom outputs
TODO: Explain Interface
TODO: Explain BaseClass

## Pausing a probe
A failing probe results in the Healthcheck returning a non 200 HTTP status code, in order to represent an error. Your monitoring software may check these values a lot. As a result incidents or escalations are generated (as well as a lot of e-mails). 

There **"Pause"** and **"Play"** come to the rescue. 


TODO: Explain concept of pausing probes.

## Inspiration

This extension was inspired by the GitHub Gist of **Axel Seemann** (aseemann). For details on the Gist see here: [healthcheck.php](https://gist.github.com/aseemann/42041fccb784cf472349a7af9748fe9b). Also did I take some ideas from the extension **t3monitoring_client** from **Ringer Georg**. For details on this extension see here: [t3monitoring_client](https://github.com/georgringer/t3monitoring_client).
## Credits
The credit for the images goes to:
- success.png: <a href="https://www.flaticon.com/free-icons/tick" title="tick icons">Tick icons created by kliwir art - Flaticon</a>
- danger.png: <a href="https://www.flaticon.com/free-icons/error" title="error icons">Error icons created by Ilham Fitrotul Hayat - Flaticon</a>
- health-report.png: <a href="https://www.flaticon.com/free-icons/medical-record" title="medical record icons">Medical record icons created by Freepik - Flaticon</a>
- tx_healthcheck_domain_model_probe_pause.png and pause.png: <a href="https://www.flaticon.com/free-icons/pause" title="pause icons">Pause icons created by Kiranshastry - Flaticon</a>
- play.png: <a href="https://www.flaticon.com/free-icons/play" title="play icons">Play icons created by Freepik - Flaticon</a>