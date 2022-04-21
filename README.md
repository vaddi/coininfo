# CoinInfo Luno/BitX Prometheus Exporter #

Gets you current Luno/BitX Wallet Data from the API and bring them into a Prometheus ready Metrics Endpoint.

> Do not operate this endpoint on a public network!

It Contains sensitiv Information about your Luno Wallets. If you want to place them on a public Webserver, you should add TLS and Basic Auth over a apache or nginx Settup and add the Credentials to your Prometheus Data (look into the examle).

Also for Security Reasons you should only use an APIKey and Secret wich is only able to read Data from your Wallets (Readonly Acces is enought for this Exporter, we dont want to buy or sell).


## Dependencies ##

You'll need the following packeges installed:

* Running Webserver (apache/nginx)
* PHP > 7.0
* PHP Curl (apt install php_curl)
* An account on [Luno][] aka BitX


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

A Dashboard Example:  
![dashboard_example](https://github.com/vaddi/coininfo/blob/main/assets/images/dashboard_example.png "Dashboard Example")  
A simple Dashboard from the Exporter Metrics to get a complete Overview over you Wallets or current courses. The example `dashboard.json` File can found below the `assets` Folder. Just Import its content to your Grafana.


## Features ##

Which Metrics will be used:

- Current Bitcoin Price from:
 - [Luno][]
 - [Bitaps][]
 - [Coindesk][]
 - [Blockchain][]
- From [Luno][] we get also Bitcoin and Ethereum the bid, aks, trade and the 24h rolling volume
- Current Balance of your Luno Wallets (Transactions for balance, reserved and unconfirmed)
- Some error Output


## Update ##

To update simply change into the Directory and exec:

    git pull

This will replace all Files, instead your `config.php` File to a new version.

Find Out if there are some newer Version available you can use this Bash oneliner:

    [ $(git -C $(pwd) rev-parse HEAD) = $(git -C $(pwd) ls-remote $(git -C $folder rev-parse --abbr} | \sed 's/\// /g') | cut -f1) ] && echo "No Updates" || echo "Updates"


## links ##

Some usefull Links:

- [Luno_Invite][]
- [Bitaps][]
- [Coindesk][]
- [Blockchain][]
- [German_Readme][]


[Luno]: https://www.luno.com/
[Luno_Invite]: https://www.luno.com/invite/RQ7AC5
[Bitaps]: https://bitaps.com/
[Coindesk]: https://coindesk.com/
[Blockchain]: https://blockchain.info/
[German_Readme]: https://github.com/vaddi/coininfo/blob/main/assets/README_DE.md
