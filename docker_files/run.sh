#!/usr/bin/env bash

# Increasing the memory limitation.
mv docker_files/php.ini /usr/local/etc/php

# Start MySQL.
echo -e "\n # Start MySQL."
service mysql start

# Configure apache2.
echo -e "\n # Configure apache2."
cp docker_files/default.apache2.conf /etc/apache2/apache2.conf
service apache2 restart

# Install Bower.
echo -e "\n # Install Bower."
npm install -g bower
echo '{ "allow_root": true }' > /root/.bowerrc

# Install drush.
echo -e "\n # Install drush."
export PATH="$HOME/.composer/vendor/bin:$PATH"
composer global require drush/drush:7.*

cd /var/www/html/openscholar
mkdir ~/.drush/
cp docker_files/aliases.drushrc.php ~/.drush/aliases.drushrc.php
source /root/.bashrc

# Install Drupal
echo -e "\n # Install Drupal"
bash docker.install.sh
chmod -R 777 /var/www/html/openscholar/www/sites/default/files/

# Install custom domains
echo -e "\n # Add lincoln virtual domain.\n"
service apache2 restart
sh -c "echo 127.0.0.1	lincoln.local >> /etc/hosts"
sh -c "echo 127.0.0.1   localhost >> /etc/hosts"
sh -c "cat openscholar/behat/lincoln-vhost.txt > /etc/apache2/sites-available/lincoln.local.conf"
cat /etc/hosts
a2ensite lincoln.local
service apache2 reload

# Download and install Apache solr.
echo -e "\n # Install apache solr."
wget https://archive.apache.org/dist/lucene/solr/3.6.2/apache-solr-3.6.2.zip
unzip -oq apache-solr-3.6.2.zip
cd apache-solr-3.6.2/example/solr/conf

# Copy the solr config files from the apache solr module
yes | cp /var/www/html/openscholar/www/profiles/openscholar/modules/contrib/apachesolr/solr-conf/solr-3.x/* .

# Copy the modified solrconfig.xml file to commit records every 20 seconds so items will show up in search sooner
yes | cp /var/www/html/openscholar/www/profiles/openscholar/behat/solr/solrconfig.xml .
cd ../../
java -jar start.jar &
sleep 10
cd /var/www/html/openscholar

# Index site using Apache solr
echo -e "\n Installing Apache solr integration."
cd www
drush en os_search_solr -y
drush solr-mark-all
drush solr-index
drush vset oembedembedly_api_key $EMBEDLYAPIKEY
cd -

# Install Firefox (iceweasel).
echo -e "\n${BGCYAN}[RUN] Install firefox. ${RESTORE}"
apt-get update
apt-get -qq -y install iceweasel > /dev/null

# Install headless GUI for browser.'Xvfb is a display server that performs
# graphical operations in memory'.
echo -e "\n [RUN] Installing XVFB (headless GUI for Firefox).\n"
apt-get install xvfb -y
apt-get install openjdk-7-jre-headless -y
Xvfb :99 -ac &
export DISPLAY=:99
sleep 5

# Install Selenium.
echo -e  "\n${BGCYAN}[RUN] Install Selenium. ${RESTORE}"
wget http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.0.jar
java -jar selenium-server-standalone-2.53.0.jar > /dev/null 2>&1 &
sleep 10

# Install behat
echo -e "\n # Install behat"
cd openscholar/behat
pwd
ls -al
curl -sS https://getcomposer.org/installer | php
php composer.phar install
cp behat.local.yml.travis behat.local.yml

if [ -v $DOCKER_DEBUG ] && [ $DOCKER_DEBUG -eq 1 ]; then
  bash
else
  # Run tests
  echo -e "\n # Run tests"
  ./bin/behat --tags=$TEST_SUITE


  if [ $? -ne 0 ]; then
    echo "Behat failed"
    exit 1
  fi
fi
