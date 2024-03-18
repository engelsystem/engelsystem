FROM alpine

RUN apk update && \
    apk add mariadb-client && \
    echo -e "\
# m	h	d	m	wd	command\n\
0   1   *   *	*   /usr/bin/mysqldump --host=\${MYSQL_HOST} --password=\`cat \${MYSQL_ROOT_PASSWORD_FILE}\` --all-databases --single-transaction > /backup/backup_\`date +%Y-%m-%d\`.sql\n\
0   2   *   *	*   /usr/bin/find /backup/ -type f -mtime +7 -name '*.sql' -execdir rm -- '{}' \;\n\
" > /root/crontab && \
    crontab /root/crontab && \
    mkdir /backup

ENTRYPOINT /usr/sbin/crond -f -l 2
