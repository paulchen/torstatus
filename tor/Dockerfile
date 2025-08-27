FROM debian:trixie-slim

RUN apt-get update && \
    apt-get -y dist-upgrade && \
    apt-get -y install monitoring-plugins-basic apt-transport-https wget gpg && \
    apt-get -y autoremove && \
    apt-get -y purge $(dpkg -l | grep '^rc' | awk '{print $2}') && \
    apt-get clean

COPY tor.list /etc/apt/sources.list.d/tor.list

RUN wget -qO- https://deb.torproject.org/torproject.org/A3C4F0F979CAA22CDBA8F512EE8CBC9E886DDD89.asc | gpg --dearmor | tee /usr/share/keyrings/deb.torproject.org-keyring.gpg >/dev/null
RUN apt-get update && \
    apt-get -y install tor-geoipdb tor deb.torproject.org-keyring && \
    rm -rf /var/lib/apt/lists/*

RUN mkdir /run/tor && chown debian-tor:debian-tor /run/tor && chmod 0700 /run/tor

COPY torrc /etc/tor/torrc
COPY updates.sh /opt

CMD [ "/usr/bin/tor", "--defaults-torrc", "/usr/share/tor/tor-service-defaults-torrc", "-f", "/etc/tor/torrc", "--RunAsDaemon", "0" ] 

