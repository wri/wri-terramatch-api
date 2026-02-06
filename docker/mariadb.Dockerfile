# MARIADB
FROM mariadb:10.6 AS mariadb
COPY docker/override.cnf /etc/mysql/mariadb.conf.d/override.cnf
