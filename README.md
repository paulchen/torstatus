# TorStatus

Web application listing Tor nodes running at [https://torstatus.rueckgr.at/](https://torstatus.rueckgr.at/). Initially developed by Joseph B. Kowalski.

## Docker-based setup (recommended)

### Prerequisites
 - Git
 - Docker together with Docker compose

### Steps
 - Clone the repository.
 - Create the Docker network `torstatus` using `docker create network torstatus`.
 - Run `docker compose build` from the root of your repository clone.
 - Run `docker compose up` to start everything.
 - Wait for the updater to finish (it will log `Script successful, waiting 900 seconds...`).
 - Point your browser to [https://localhost:8765](https://localhost:8765).

### Things to note
 - The first start-up will take more time as the database needs to be initialized.
 - As long as the database is not up, the updater will complain about not being able to connect to the database. This is fine; as soon as the database is up, the updater will connect to it and start running.
 - The first run of the updater after every startup will take longer than the subsequent runs. This is because memcached has not yet been initialized.
 - The tor process will complain about its control port being accessible from non-local addresses. This warning can be ignored as it is only accessible from the containers of the TorStatus application.
 - All containers feature a health check that you can observe, e.g. using `docker ps`.

### Environment variables
 - `REAL_SERVER_IP`: The public IPv4 address of the TorStatus instance. Used for determining whether a certain Tor exit node will allow connecting to this TorStatus instance.
 - `HIDDEN_SERVICE_URL`: self-explanatory

### Reverse proxy

If you intend run a web server as a reverse proxy in front of TorStatus, there are two options where to forward incoming requests: Either to nginx or to PHP-FPM.

#### Forward requests to nginx

The `nginx` container exposes port `8765`. You can forward requests there, e.g. in Apache with `mod_proxy`:

```
ProxyPass / https://127.0.0.1:8765/
ProxyPassReverse / https://127.0.0.1:8765/
```

The disadvantage of this approach is that requests to PHP files will be forwarded twice, once by your reverse proxy and once by nginx in the `nginx` container. To avoid this, use the below approach to directly forward PHP requests to PHP-FPM.

#### Forward requests to PHP-FPM

The `php-fpm` container exposes port `9001`. You can have your reverse proxy handle static content from the `nginx/web` directory and forward requests for PHP files to that port, e.g. in Apache:

```
<FilesMatch ".+\.ph(ar|p|tml)$">
  ProxyFCGISetEnvIf "true" SCRIPT_FILENAME "/var/www/html%{reqenv:SCRIPT_NAME}"
  SetHandler "proxy:fcgi://127.0.0.1:9001"
</FilesMatch>
```

### Hidden services

 - The directory `tor/hidden_services` will be mounted at `/var/lib/tor/hidden_services` inside the container. Place the files for your hidden services there.
 - Add a file to the `tor/torrc.d` directory configuring your hidden services using the `HiddenService*` directives. Use `/var/lib/tor/hidden_services/...` for `HiddenServiceDir` and keep the above mount in mind.

### Logging

 - All containers' logs are sent to journald.


## Setup without docker

### Tor

 - You need access to a running Tor daemon (client, middle node, exit node).
 - Configure a control port with a password (settings `ControlPort` and `HashedControlPassword` in `torrc`).
 - Additionally, set `UseMicrodescriptors` to `0` in `torrc`.

### memcached

Set up an instance of memcached.

### MariaDB

Set up MariaDB, create a database with a user, and populate the database using [mariadb/sql/install.sql](mariadb/sql/install.sql).

### Web application

 - Copy [nginx/web/config\_template.php](nginx/web/config_template.php) to `config.php` and modify it to your needs.
 - Set up a web server with PHP support (e.g. Apache or nginx with PHP-FPM).
 - You will need the PHP modules `memcached`, `mysqli`, and `gd`.
 - Configure your web server (or a separate vhost) to serve content from `nginx/web`.

### Updater

 - Set up Perl.
 - You need these CPAN modules: `DBI PHP::Serialization LWP::Simple File::Touch Parallel::ForkManager Cache::Memcached Net::IP DBD::MariaDB`
 - `cd` to the directory `updater` and invoke `tns_update.pl` there.
 - Use Cron to invoke `tns_update.pl` regularly. Alternatively, you may launch `updater.sh` once after each reboot.

