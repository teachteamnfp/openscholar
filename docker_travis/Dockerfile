FROM gizra/drupal-lamp

ADD .. /var/www/html/openscholar
WORKDIR /var/www/html/openscholar

# Add a bash script to finalize all
RUN chmod +x /var/www/html/openscholar/docker_travis/docker_files/run.sh
ENTRYPOINT ["/var/www/html/openscholar/docker_travis/docker_files/run.sh"]

EXPOSE 80 3306 22 8983
