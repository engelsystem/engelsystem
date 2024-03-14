FROM alpine

RUN apk update && \
    apk add mariadb-client && \
    echo -e "\
# m	h	d	m	wd	command\n\
0   *   *   *   *   /usr/bin/mysqldump --host=\${MYSQL_HOST} --password=\`cat \${MYSQL_ROOT_PASSWORD_FILE}\` --all-databases --single-transaction --ignore-table-data=\${MYSQL_DATABASE}.sessions > /backup/backup_\`date +%Y-%m-%d-%H-%M\`.sql\n\
43  3   *   *   *   /usr/bin/find /backup/ -type f -mtime +3 -name '*.sql' -delete\n\
" > /root/crontab && \
    crontab /root/crontab && \
    mkdir /backup

ENTRYPOINT /usr/sbin/crond -f -l 2
