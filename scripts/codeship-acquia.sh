# Quick codeship script to push builds to a pair of acquia repos as new branches are made.

# Get PR branch, default to empty string
PR_BRANCH=$(git show -s --format=%B $CI_COMMIT_ID | grep -oP 'Merge pull request #[\d]* from openscholar/\K(.*)' || echo "")
echo "'$PR_BRANCH' set as PR branch."
# pull down the acquia branch
mkdir -p ~/src/acquia.com/
git config --global user.email "openscholar@swap.lists.harvard.edu"
git config --global user.name "OpenScholar Auto Push Bot"
if git ls-remote --heads hwpi1@svn-1745.prod.hosting.acquia.com:hwpi1.git | grep -sw $CI_BRANCH 2>&1>/dev/null; then
git clone -b $CI_BRANCH hwpi1@svn-1745.prod.hosting.acquia.com:hwpi1.git  ~/src/acquia.com/hwpi1;
cd ~/src/acquia.com/hwpi1
else
git clone -b SCHOLAR-3.x hwpi1@svn-1745.prod.hosting.acquia.com:hwpi1.git  ~/src/acquia.com/hwpi1;
cd ~/src/acquia.com/hwpi1;
git checkout -b $CI_BRANCH;
fi
if ! test "$PR_BRANCH" = ""; then
# do things
# This branch is probably deleted, or will be soon, so we don't need to build
git push origin :$PR_BRANCH || echo "$PR_BRANCH not found on hwpi1"
fi
# Build this branch and push it to Acquia

# Set up global configuration and install tools needed to build
composer global require drush/drush
echo -e "Drush Downloaded via composor\n"
mkdir -p ~/.drush
printf "disable_functions =\nmemory_limit = 256M\ndate.timezone = \"America/New_York\"" > ~/.drush/php.ini
export PATH="$HOME/.composer/vendor/bin:$PATH"
drush --version 2> /dev/null || exit 1
npm install -g bower
npm install -g node-sass

echo $CI_BRANCH
echo $CI_COMMIT_ID
# Drush executable.
[[ $DRUSH && ${DRUSH-x} ]] || DRUSH=drush
BUILD_ROOT='/home/rof/src/acquia.com/hwpi1'
cd $BUILD_ROOT
#List of files from docroot that should be preserved
preserve_files=( .htaccess robots_disallow.txt sites 404_fast.html favicon.ico )
#Backup the make files
cp -f openscholar/openscholar/drupal-org-core.make /tmp/
cp -f openscholar/openscholar/drupal-org.make /tmp/
cp -f openscholar/openscholar/bower.json /tmp/
git subtree pull -m "subtree merge in codeship" --prefix=openscholar git://github.com/openscholar/openscholar.git $CI_BRANCH
#Only build if no build has ever happened, or if the make files have changed
if [ ! -d openscholar/openscholar/modules/contrib ] || [ $FORCE_REBUILD == "1" ] || [ "$(cmp -b 'openscholar/openscholar/drupal-org-core.make' '/tmp/drupal-org-core.make')" != "" ] || [ "$(cmp -b 'openscholar/openscholar/drupal-org.make' '/tmp/drupal-org.make')" != "" ] || [ "$(cmp -b 'openscholar/openscholar/bower.json' '/tmp/bower.json')" != "" ]; then
# Chores.
for DIR in $BUILD_ROOT/www-build $BUILD_ROOT/www-backup openscholar/openscholar/1 openscholar/openscholar/modules/contrib openscholar/openscholar/themes/contrib openscholar/openscholar/libraries; do
	rm -Rf $DIR
done
cd openscholar/openscholar

$DRUSH make --no-core --contrib-destination drupal-org.make .
(
	# Download composer components
	composer install
	rm -rf libraries/git/symfony/event-dispatcher/Symfony/Component/EventDispatcher/.git
	rm -f libraries/git/symfony/event-dispatcher/Symfony/Component/EventDispatcher/.gitignore
	git rm -r --cached libraries/git/symfony/event-dispatcher/Symfony/Component/EventDispatcher
	rm -rf libraries/git/symfony/process/Symfony/Component/Process/.git
	rm -f libraries/git/symfony/process/Symfony/Component/Process/.gitignore
	git rm -r --cached libraries/git/symfony/process/Symfony/Component/Process

	# Get the angular components
	bower -q install
)

(
  # Build CSS files from SASS
  for DIR in modules/*; do
    if [ -d $DIR ] && [ -d $DIR/os_style_override ] ; then
      node-sass $DIR/os_style_override/sass -o $DIR/os_style_override/css
    fi
  done
)

cd ../../
$DRUSH make openscholar/openscholar/drupal-org-core.make $BUILD_ROOT/www-build

# Backup files from existing installation.
cd $BUILD_ROOT
DOCROOT='docroot';
for BACKUP_FILE in "${preserve_files[@]}"; do
	rm -Rf www-build/$BACKUP_FILE
	mv $DOCROOT/$BACKUP_FILE www-build/
done
# Move the profile in place.
ln -s ../../openscholar/openscholar $BUILD_ROOT/www-build/profiles/openscholar
# link up phpmyadmin
# ln -s ../phpMyAdmin-3.5.2.2-english $BUILD_ROOT/www-build/phpmyadmin
#link up js.php
ln -s ../openscholar/openscholar/modules/contrib/js/js.php $BUILD_ROOT/www-build/js.php
# Fix permisions before deleting.
# chmod -R +w $BUILD_ROOT/$DOCROOT/sites/* || true
rm -Rf $BUILD_ROOT/$DOCROOT || true
#remove install.php
rm -Rf $BUILD_ROOT/www-build/install.php || true
# Restore updated site.
mv $BUILD_ROOT/www-build $BUILD_ROOT/$DOCROOT
# Add New Files to repo and commit changes
git add --all $BUILD_ROOT/$DOCROOT
#Copy unmakable modules
cp -R openscholar/temporary/* openscholar/openscholar/modules/contrib/
# iCalcreator cannot be downloaded via make because a temporary token is needed,
# so we have the library inside os_events directory and we copy it to libraries.
cp -R openscholar/openscholar/modules/os_features/os_events/iCalcreator openscholar/openscholar/libraries/
# Download the git wrapper library using the composer.

for DIR in openscholar/openscholar/libraries openscholar/openscholar/themes/contrib openscholar/openscholar/modules/contrib
do
if [ -d "$DIR" ]; then
git add --all -f $DIR
fi
done
git commit -a -m "Make File Update."
#END BUILD PROCESS
else
#Copy unmakable modules, when we don’t build
cp -R openscholar/temporary/* openscholar/openscholar/modules/contrib/
git commit -a -m "Update Temporary Modules." || echo 'Nothing to commit.'
fi
git push origin $CI_BRANCH
echo -e "\033[1;36mFINISHED BUILDING $CI_BRANCH ON HWPI1\e[0m"
# space so I can see where one ends and another begins








# pull down the acquia branch
mkdir -p /home/rof/src/acquia.com/
if git ls-remote --heads hwpi2@svn-1745.prod.hosting.acquia.com:hwpi2.git | grep -sw $CI_BRANCH 2>&1>/dev/null; then
git clone -b $CI_BRANCH hwpi2@svn-1745.prod.hosting.acquia.com:hwpi2.git  /home/rof/src/acquia.com/hwpi2;
cd /home/rof/src/acquia.com/hwpi2
else
git clone -b SCHOLAR-3.x hwpi2@svn-1745.prod.hosting.acquia.com:hwpi2.git  /home/rof/src/acquia.com/hwpi2;
cd /home/rof/src/acquia.com/hwpi2;
git checkout -b $CI_BRANCH;
fi
if ! test "$PR_BRANCH" = ""; then
# do things
# This branch is probably deleted, or will be soon, so we don't need to build
git push origin :$PR_BRANCH || echo "$PR_BRANCH not found on hwpi2"
fi
# Build this branch and push it to Acquia
BUILD_ROOT='/home/rof/src/acquia.com/hwpi2'
cd $BUILD_ROOT
#List of files from docroot that should be preserved
preserve_files=( .htaccess robots_disallow.txt sites 404_fast.html favicon.ico )
#Backup the make files
cp -f openscholar/openscholar/drupal-org-core.make /tmp/
cp -f openscholar/openscholar/drupal-org.make /tmp/
cp -f openscholar/openscholar/bower.json /tmp/
git subtree pull -m "subtree merge in codeship" --prefix=openscholar git://github.com/openscholar/openscholar.git $CI_BRANCH
#Only build if no build has ever happened, or if the make files have changed
if [ ! -d openscholar/openscholar/modules/contrib ] || [ $FORCE_REBUILD == "1" ] || [ "$(cmp -b 'openscholar/openscholar/drupal-org-core.make' '/tmp/drupal-org-core.make')" != "" ] || [ "$(cmp -b 'openscholar/openscholar/drupal-org.make' '/tmp/drupal-org.make')" != "" ] || [ "$(cmp -b 'openscholar/openscholar/bower.json' '/tmp/bower.json')" != "" ]; then
# Chores.
for DIR in $BUILD_ROOT/www-build $BUILD_ROOT/www-backup openscholar/openscholar/1 openscholar/openscholar/modules/contrib openscholar/openscholar/themes/contrib openscholar/openscholar/libraries; do
	rm -Rf $DIR
done
cd openscholar/openscholar
$DRUSH make --no-core --contrib-destination drupal-org.make .
(
	# Download composer components
	composer install
	rm -rf libraries/git/symfony/event-dispatcher/Symfony/Component/EventDispatcher/.git
	rm -f libraries/git/symfony/event-dispatcher/Symfony/Component/EventDispatcher/.gitignore
	git rm -r --cached libraries/git/symfony/event-dispatcher/Symfony/Component/EventDispatcher
	rm -rf libraries/git/symfony/process/Symfony/Component/Process/.git
	rm -f libraries/git/symfony/process/Symfony/Component/Process/.gitignore
	git rm -r --cached libraries/git/symfony/process/Symfony/Component/Process

	# Get the angular components
	bower -q install
)
(
  # Build CSS files from SASS
  for DIR in modules/*; do
    if [ -d $DIR ] && [ -d $DIR/os_style_override ] ; then
      node-sass $DIR/os_style_override/sass -o $DIR/os_style_override/css
    fi
  done
)
cd ../..
# Build core.
$DRUSH make openscholar/openscholar/drupal-org-core.make $BUILD_ROOT/www-build
# Backup files from existing installation.
cd $BUILD_ROOT
for BACKUP_FILE in "${preserve_files[@]}"; do
	rm -Rf www-build/$BACKUP_FILE
	mv $DOCROOT/$BACKUP_FILE www-build/
done
# Move the profile in place.
ln -s ../../openscholar/openscholar $BUILD_ROOT/www-build/profiles/openscholar
# link up phpmyadmin
# ln -s ../phpMyAdmin-3.5.2.2-english $BUILD_ROOT/www-build/phpmyadmin
#link up js.php
ln -s ../openscholar/openscholar/modules/contrib/js/js.php $BUILD_ROOT/www-build/js.php
# Fix permisions before deleting.
chmod -R +w $BUILD_ROOT/$DOCROOT/sites/* || true
rm -Rf $BUILD_ROOT/$DOCROOT || true
#remove install.php
rm -Rf $BUILD_ROOT/www-build/install.php || true
# Restore updated site.
mv $BUILD_ROOT/www-build $BUILD_ROOT/$DOCROOT
# Add New Files to repo and commit changes
git add --all $BUILD_ROOT/$DOCROOT
#Copy unmakable modules
cp -R openscholar/temporary/* openscholar/openscholar/modules/contrib/
# iCalcreator cannot be downloaded via make because a temporary token is needed,
# so we have the library inside os_events directory and we copy it to libraries.
cp -R openscholar/openscholar/modules/os_features/os_events/iCalcreator openscholar/openscholar/libraries/
#Build the ops profile
(
	if [ -n "$OPSDIR" ] && [ -d $BUILD_ROOT/$DOCROOT/$OPSDIR ] ; then
	cd $BUILD_ROOT/$DOCROOT/$OPSDIR
	$DRUSH make --no-core --contrib-destination $BUILD_ROOT/openscholar/openscholar/os-ops.make .
	fi
)
for DIR in openscholar/openscholar/libraries openscholar/openscholar/themes/contrib openscholar/openscholar/modules/contrib; do
	if [ -d "$DIR" ]; then
		git add --all -f $DIR
	fi
done
git commit -a -m "Make File Update."
#END BUILD PROCESS
else
#Copy unmakable modules, when we don’t build
	cp -R openscholar/temporary/* openscholar/openscholar/modules/contrib/
	git commit -a -m "Update Temporary Modules."  || echo 'Nothing to commit.'
fi
git push origin $CI_BRANCH
echo "FINISHED BUILDING $CI_BRANCH ON HWPI2"
