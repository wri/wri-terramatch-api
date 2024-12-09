FROM docker.osgeo.org/geoserver:2.25.x

COPY docker/web.xml /usr/local/tomcat/webapps/geoserver/WEB-INF/web.xml

COPY docker/geoserver/mysql-connector-java-8.0.28.jar /usr/local/tomcat/webapps/geoserver/WEB-INF/lib/mysql-connector-java-8.0.28.jar

RUN mkdir -p /opt/geoserver_data/workspaces/wri
COPY docker/geoserver/wri /opt/geoserver_data/workspaces/wri
