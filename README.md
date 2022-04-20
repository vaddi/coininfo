# CoinInfo Luno/BitX Prometheus Exporter #

Gets you current Luno/BitX Wallet Data from the API and bring them into a Prometheus ready Metrics Endpoint.

Do not operate this endpoint on a public network!
It Contains sensitiv Information about your Luno Wallets. If you want to place them on a public Webserver, you should add TLS and Basic Auth over a apache or nginx Settup and add the Credentials to your Prometheus Data (look into the examle)

Also for Security Reasons you should only use an APIKey and Secret wich is only able to Read Data from your Wallets (Readonly Acces is enought for this Exporter, we dont want to buy or sell).


## Installation ##

Move to a Folder where a PHP Webserver can serve the PHP files. Here i use the default Foldername `coininfo`, but you can rename them to whatever you need.

Get the Repository Data from GitHub:

    git clone git@github.com:vaddi/coininfo.git

Copy the Configuration example file:

    cp config.php.example config.php

Edit the File `config.php` with a Texteditor and insert your API Key and Secret:

```PHP
define( 'APIKEY', 'MySecretKey' );
define( 'APISEC', 'MyAPISecret' );
```

Validate the Setup by checking the URL in a WebBrowser, there shouldn't occure any Errors, only Plaintext Metrics. If anything is fine, your'e ready to add the URL to your Prometheus to collect the Metrics. Here is an Example of how i add them:

```yaml
...
  - job_name: 'coininfo'
    scrape_interval: 1m
    scrape_timeout: 30sm
    metrics_path: '/coininfo'
#    basic_auth:
#      username: 'XXXXX'
#      password: 'XXXXX'
    static_configs:
      - targets: ['myserver.domain.tld:443'],
...
```

An example `dashboard.json` File is also available. Just Import this to your Grafana.


## Update ##

To update simply change into the Directory and exec:

    git pull

This will replace all Files, instead your `config.php` File to a new version.

Find Out if there are some newer Version available you can use this Bash oneliner:

    [ $(git -C $(pwd) rev-parse HEAD) = $(git -C $(pwd) ls-remote $(git -C $folder rev-parse --abbr} | \sed 's/\// /g') | cut -f1) ] && echo "No Updates" || echo "Updates"



