# CoinInfo Luno/BitX Prometheus Exporter #

Holt die akutellen Luno/BitX Wallet Daten von der API und gibt diese in einem Prometheus Metriken Format als webseiteEndpunkt aus.

Benutze diesen Endpunkt nicht ungesichert im Internet!
Er Enthält Sensitive Informationen über ihre Luno Wallets. Wenn du Ihn auf einem öffentlichen Server nutzen möchtest, solltest du ihn dementsprechend mit TLS und einer Basic Authentifizierung absichern.

Ebenfalls aus Sicherheitsgründen solltest du die API Key und Secret Kombination nur mit einem Readonly Token verwenden. Dieser Anwendung reicht der Lesende Zugriff aus, wir wollen hier nicht kaufen oder verkaufen.


## Installation ##

Wechsle in das Verzeichnis, das von einem Webserver aus PHP Dateien verarbeiten und Ausgeben kann. Ich verwendet hier im Beispiel den Standard Ordnernamen `coininfo`, du kannst den Ordner aber auch umbenennen.

Holen der Repository Daten von GitHub:

    git clone git@github.com:vaddi/coininfo.git

Kopieren Der Konfigurations Beispieldatei:

    cp config.php.example config.php

Bearbeiten der Datei `config.php` mit einem Texteditor, hinterlege hier deinen Apikey und das zugehörige Secret:

```PHP
define( 'APIKEY', 'MySecretKey' );
define( 'APISEC', 'MyAPISecret' );
```

Überprüfe den Exporter, indem du die Adresse in einem Browser aufrufst. Es sollten keine Fehlermeldungen ausgegeben werden, nur Klartext Metriken im Prometheus Format. 
Wenn alles ok ist, kannst du die Adresse jetzt in deiner Prometheus Konfiguration als neuen Endpunkt hinterlegen, damit dieser anfängt die Daten zu sammeln. Ein BEispiel wie die Konfiguration aussehen könnte:

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

Eine Beispiel `dashboard.json` Datei is ebenfalls enthalten. Importiere den Inhalt der json Datei einfach in einem neuen Grafana Dashboard.


## Update ##

Zum Updaten der Anwendung brauchst du nur den folgenden Befehl durchzuführen:

    git pull

Das wird alle Dateien, ausser der `config.php` Datei, auf den neusten Stand bringen.

Um herrauszufinden ob es Updates im Repository gitb, kannst du diesen Bash Einzeiler verwenden:

    [ $(git -C $(pwd) rev-parse HEAD) = $(git -C $(pwd) ls-remote $(git -C $folder rev-parse --abbr} | \sed 's/\// /g') | cut -f1) ] && echo "No Updates" || echo "Updates"

