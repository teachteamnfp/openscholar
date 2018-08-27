#!/usr/bin/env bash

cd /opt/apache-solr/apache-solr-3.6.2/example/solr/conf
yes | cp /var/www/html/www/profiles/openscholar/modules/contrib/apachesolr/solr-conf/solr-3.x/* .
yes | cp /var/www/html/www/profiles/openscholar/behat/solr/solrconfig.xml .
cd /opt/apache-solr/apache-solr-3.6.2/example
java -jar start.jar &
sleep 10
cd /var/www/html/openscholar/behat
composer install
cp behat.local.yml.travis behat.local.yml

if [ "${TEST_SUITE}" = 'restful' ]; then
    # Clear cache twice for restful
    cd /var/www/html/openscholar/www/
    echo -e "\n # GET api/blog/12 try1"
    wget http://localhost/api/blog/12
    drush cache-clear all
    echo -e "\n # GET api/blog/12 try2"
    wget http://localhost/api/blog/12
    drush cache-clear all
    echo -e "\n # GET api/blog/12 try3"
    wget http://localhost/api/blog/12
fi

if [ $DOCKER_DEBUG -eq 1 ]; then
  bash
else
  # Run tests
  echo -e "\n # Run tests with tag: ${TEST_SUITE}"
  ./bin/behat --tags="${TEST_SUITE}" --strict

  if [ $? -ne 0 ]; then
    echo "Behat failed"
    exit 1
  fi
fi
