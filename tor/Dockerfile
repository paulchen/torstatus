FROM debian:bookworm-slim

RUN apt-get update
RUN apt-get -y dist-upgrade
RUN apt-get -y install monitoring-plugins-basic apt-transport-https wget gpg
RUN apt-get -y autoremove
RUN apt-get -y purge $(dpkg -l | grep '^rc' | awk '{print $2}')
RUN apt-get clean

COPY tor.list /etc/apt/sources.list.d/tor.list

RUN wget -qO- https://deb.torproject.org/torproject.org/A3C4F0F979CAA22CDBA8F512EE8CBC9E886DDD89.asc | gpg --dearmor | tee /usr/share/keyrings/deb.torproject.org-keyring.gpg >/dev/null
RUN apt-get update && apt-get -y install tor-geoipdb tor deb.torproject.org-keyring

RUN mkdir /run/tor && chown debian-tor:debian-tor /run/tor && chmod 0700 /run/tor

COPY torrc /etc/tor/torrc
COPY updates.sh /opt

CMD [ "/usr/bin/tor", "--defaults-torrc", "/usr/share/tor/tor-service-defaults-torrc", "-f", "/etc/tor/torrc", "--RunAsDaemon", "0" ] 

