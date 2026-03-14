FROM mariadb:10.7

COPY docker/backup.crontab /root/crontab

RUN apt update && \
    apt install -y cron strace && \
    crontab /root/crontab && \
    mkdir /backup

ENTRYPOINT /usr/sbin/cron -f -L 7
