<!-- omit in toc -->
# TYPO3 extension: "healthcheck"

- [Inspiration](#inspiration)
- [Configuration](#configuration)
- [Customization](#customization)
  - [Add custom probes](#add-custom-probes)
  - [Add custom outputs](#add-custom-outputs)
- [Used resources](#used-resources)


## Inspiration

This extension was inspired by the GitHub Gist of **Axel Seemann** (aseemann). For details on the Gist see here: [healthcheck.php](https://gist.github.com/aseemann/42041fccb784cf472349a7af9748fe9b).

Also I took some ideas from the extension **t3monitoring_client** from **Ringer Georg**. For details on this extension see here: [t3monitoring_client]()

## Configuration
TODO

## Customization

This extension is customizable in a way, that allows the developer to add custom **probes** and **outputs**. The extension comes with the following already installed:

Probes:
- **CacheProbe:** Checks if all cache configurations are writeable
- **DatabaseProbe:** Checks if the access to the database works
- **SchedulerProbe:** Check if there are any scheduled tasks, which have a failure

Outputs:
- **HtmlOutput:** Renders a HTML page using Bootstrap 5 to display the HealthcheckResult. Showing a overall Success or Error.
- **JsonOutput:** Simple JSON output which displays the HealthcheckResult as JSON.

### Add custom probes
TODO

### Add custom outputs
TODO

## Used resources

The credit for the images goes to:
- success.png: <a href="https://www.flaticon.com/free-icons/tick" title="tick icons">Tick icons created by kliwir art - Flaticon</a>
- danger.png: <a href="https://www.flaticon.com/free-icons/error" title="error icons">Error icons created by Ilham Fitrotul Hayat - Flaticon</a>