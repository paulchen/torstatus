FROM perl:5

RUN apt-get update
RUN apt-get -y dist-upgrade
RUN apt-get -y install monitoring-plugins-basic
RUN apt-get -y autoremove
RUN apt-get -y purge $(dpkg -l | grep '^rc' | awk '{print $2}')
RUN apt-get clean

RUN cpanm DBI PHP::Serialization LWP::Simple File::Touch Parallel::ForkManager Cache::Memcached Net::IP DBD::MariaDB
RUN mkdir /opt/torstatus

ADD tns_update.pl /opt/torstatus
ADD updater.sh /opt/torstatus

RUN addgroup --gid 1026 updater && adduser --disabled-password --ingroup updater --system updater && chown -R updater:updater /opt/torstatus
USER updater

CMD [ "/opt/torstatus/updater.sh" ]

