#!/usr/bin/env bash

cd /opt/apache-solr/apache-solr-3.6.2/example/solr/conf
yes | cp /var/www/html/www/profiles/openscholar/modules/contrib/apachesolr/solr-conf/solr-3.x/* .
yes | cp /var/www/html/www/profiles/openscholar/behat/solr/solrconfig.xml .
cd /opt/apache-solr/apache-solr-3.6.2/example
java -jar start.jar &
sleep 10
cd /var/www/html/www