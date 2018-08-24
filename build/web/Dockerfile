FROM centos:centos7.5.1804

RUN yum install -y epel-release # for nginx
RUN yum install -y initscripts  # for old "service"
RUN yum -y install nano

ENV container docker

RUN (cd /lib/systemd/system/sysinit.target.wants/; for i in *; do [ $i == \
systemd-tmpfiles-setup.service ] || rm -f $i; done); \
rm -f /lib/systemd/system/multi-user.target.wants/*;\
rm -f /etc/systemd/system/*.wants/*;\
rm -f /lib/systemd/system/local-fs.target.wants/*; \
rm -f /lib/systemd/system/sockets.target.wants/*udev*; \
rm -f /lib/systemd/system/sockets.target.wants/*initctl*; \
rm -f /lib/systemd/system/basic.target.wants/*;\
rm -f /lib/systemd/system/anaconda.target.wants/*;
VOLUME [ "/sys/fs/cgroup" ]

# named (dns server) service
RUN yum install -y bind bind-utils
RUN systemctl enable named.service

RUN yum -y install httpd; systemctl enable httpd.service
RUN rpm -Uvh http://rpms.remirepo.net/enterprise/remi-release-7.rpm
RUN yum -y install yum-utils
RUN yum -y update
RUN yum-config-manager --enable remi-php72
RUN yum -y install unzip which wget java-1.8.0-openjdk.x86_64
RUN yum -y install php git
RUN yum -y install php-mysqlnd php-pdo
RUN yum -y install php-gd php-ldap php-odbc php-pear php-xml php-xmlrpc php-mbstring php-soap curl curl-devel php-xdebug php-zip
RUN yum -y install http://dev.mysql.com/get/mysql-community-release-el7-5.noarch.rpm
RUN yum -y install mysql-community-server; systemctl enable mysqld.service
RUN yum -y install nodejs
RUN yum clean all

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    mkdir /opt/drush && \
    cd /opt/drush && \
    composer require drush/drush:8.* && \
    ln -s /opt/drush/vendor/bin/drush /usr/local/bin/drush

RUN npm install -g bower

RUN mkdir /opt/apache-solr && \
    cd /opt/apache-solr && \
    wget https://archive.apache.org/dist/lucene/solr/3.6.2/apache-solr-3.6.2.zip && \
    unzip -oq apache-solr-3.6.2.zip


COPY docker_files/httpd.conf /etc/httpd/conf/httpd.conf
COPY docker_files/php.ini /etc/php.ini

EXPOSE 80
EXPOSE 3306

WORKDIR /var/www/html

CMD ["/usr/sbin/init"]
