FROM perl:5

RUN cpanm DBI PHP::Serialization
RUN cpanm LWP::Simple
RUN cpanm File::Touch
RUN cpanm Parallel::ForkManager
RUN cpanm Cache::Memcached
RUN cpanm Net::IP
RUN cpanm DBD::MariaDB

CMD [ "/opt/torstatus/updater/updater.sh" ]

