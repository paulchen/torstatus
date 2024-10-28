FROM debian:bookworm-slim

RUN apt-get update

RUN apt-get -y install apt-transport-https wget gpg

COPY tor.list /etc/apt/sources.list.d/tor.list

RUN wget -qO- https://deb.torproject.org/torproject.org/A3C4F0F979CAA22CDBA8F512EE8CBC9E886DDD89.asc | gpg --dearmor | tee /usr/share/keyrings/deb.torproject.org-keyring.gpg >/dev/null
RUN apt-get update && apt-get -y install tor-geoipdb tor deb.torproject.org-keyring

RUN mkdir /run/tor && chown debian-tor:debian-tor /run/tor && chmod 0700 /run/tor

COPY torrc /etc/tor/torrc

CMD [ "/usr/bin/tor", "--defaults-torrc", "/usr/share/tor/tor-service-defaults-torrc", "-f", "/etc/tor/torrc", "--RunAsDaemon", "0" ] 

