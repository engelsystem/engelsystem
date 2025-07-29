FROM alpine

RUN apk update && \
    apk add mariadb-client && \
    echo -e "\
# m	h	d	m	wd	command\n\
0   *   *   *   *   /usr/bin/mariadb-dump --host=\${MYSQL_HOST} --skip-ssl --password=\`cat \${MYSQL_ROOT_PASSWORD_FILE}\` --single-transaction --ignore-table-data=\${MYSQL_DATABASE}.sessions --ignore-table-data=\${MYSQL_DATABASE}.log_entries \${MYSQL_DATABASE} > /backup/backup_\`date +%Y-%m-%d-%H-%M\`_daily.sql\n\
17  1   *   *   0   /usr/bin/mariadb-dump --host=\${MYSQL_HOST} --skip-ssl --password=\`cat \${MYSQL_ROOT_PASSWORD_FILE}\` --all-databases --single-transaction > /backup/backup_\`date +%Y-%m-%d-%H-%M\`_weekly.sql\n\
41  3   *   *   *   /usr/bin/find /backup/ -type f -mtime +3 -name '*_daily.sql' -delete\n\
42  3   *   *   *   /usr/bin/find /backup/ -type f -mtime +15 -name '*_weekly.sql' -delete\n\
" > /root/crontab && \
    crontab /root/crontab && \
    mkdir /backup

ENTRYPOINT /usr/sbin/crond -f -l 2
