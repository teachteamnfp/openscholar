# Quick codeship script to push builds to a pair of acquia repos as new branches are made.

# Get PR branch, default to empty string
PR_BRANCH=$(git show -s --format=%B $CI_COMMIT_ID | grep -oP 'Merge pull request #[\d]* from openscholar/\K(.*)' || echo "")
echo "'$PR_BRANCH' set as PR branch."


# pull down the acquia branch
mkdir -p ~/src/amazon/
git config --global user.email "openscholar@swap.lists.harvard.edu"
git config --global user.name "OpenScholar Auto Push Bot"
if git ls-remote --heads git@bitbucket.org:openscholar/deploysource.git | grep -sw $CI_BRANCH 2>&1>/dev/null; then
git clone -b $CI_BRANCH git@bitbucket.org:openscholar/deploysource.git  ~/src/amazon/hwpi1;
cd ~/src/amazon/hwpi1
else
git clone -b SCHOLAR-3.x git@bitbucket.org:openscholar/deploysource.git  ~/src/amazon/hwpi1;
cd ~/src/amazon/hwpi1
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
mkdir ~/.drush
printf "disable_functions =\nmemory_limit = 256M\ndate.timezone = \"America/New_York\"" > ~/.drush/php.ini
drush --version 2> /dev/null || exit 1
npm install -g bower

echo $CI_BRANCH
echo $CI_COMMIT_ID
# Drush executable.
[[ $DRUSH && ${DRUSH-x} ]] || DRUSH=drush
BUILD_ROOT='/home/rof/src/amazon/hwpi1'
cd $BUILD_ROOT
#List of files from docroot that should be preserved
preserve_files=( .htaccess robots_disallow.txt sites 404_fast.html favicon.ico )
#Backup the make files
cp -f openscholar/drupal-org-core.make /tmp/
cp -f openscholar/drupal-org.make /tmp/
cp -f openscholar/bower.json /tmp/
git subtree pull -m "subtree merge in codeship" --prefix=openscholar git://github.com/openscholar/openscholar.git $CI_BRANCH
#Only build if no build has ever happened, or if the make files have changed
pwd
if [ ! -d openscholar/modules/contrib ] || [ $FORCE_REBUILD == "1" ] || [ "$(cmp -b 'openscholar/drupal-org-core.make' '/tmp/drupal-org-core.make')" != "" ] || [ "$(cmp -b 'openscholar/drupal-org.make' '/tmp/drupal-org.make')" != "" ] || [ "$(cmp -b 'openscholar/bower.json' '/tmp/bower.json')" != "" ]; then
# Chores.
echo "Rebuilding..."
for DIR in $BUILD_ROOT/www-build $BUILD_ROOT/www-backup openscholar/1 openscholar/modules/contrib openscholar/themes/contrib openscholar/libraries; do
	rm -Rf $DIR &> /dev/null
done
cd openscholar

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

cd ../
$DRUSH make openscholar/drupal-org-core.make $BUILD_ROOT/www-build

# Backup files from existing installation.
cd $BUILD_ROOT
DOCROOT='web';
for BACKUP_FILE in "${preserve_files[@]}"; do
	rm -Rf www-build/$BACKUP_FILE
	mv $DOCROOT/$BACKUP_FILE www-build/
done
# Move the profile in place.
ln -s ../../openscholar $BUILD_ROOT/www-build/profiles/openscholar

# link up phpmyadmin
# ln -s ../phpMyAdmin-3.5.2.2-english $BUILD_ROOT/www-build/phpmyadmin

#link up js.php
ln -s ../openscholar/modules/contrib/js/js.php $BUILD_ROOT/www-build/js.php

# Fix permissions before deleting.
# chmod -R +w $BUILD_ROOT/$DOCROOT/sites/* || true
rm -Rf $BUILD_ROOT/$DOCROOT || true

#remove install.php
rm -Rf $BUILD_ROOT/www-build/install.php || true

#remove automatic testing files and tools
rm -rf $BUILD_ROOT/openscholar/behat &> /dev/null

# Restore updated site.
mv $BUILD_ROOT/www-build $BUILD_ROOT/$DOCROOT
# Add New Files to repo and commit changes
git add --all $BUILD_ROOT/$DOCROOT
#Copy unmakable modules
cp -R openscholar/temporary/* openscholar/openscholar/modules/contrib/
# iCalcreator cannot be downloaded via make because a temporary token is needed,
# so we have the library inside os_events directory and we copy it to libraries.
cp -R openscholar/modules/os_features/os_events/iCalcreator openscholar/libraries/
# Download the git wrapper library using the composer.

for DIR in openscholar/libraries openscholar/themes/contrib openscholar/modules/contrib
do
if [ -d "$DIR" ]; then
git add --all -f $DIR
fi
done
git commit -a -m "Make File Update."
#END BUILD PROCESS
else
#Copy unmakable modules, when we don’t build
cp -R temporary/* openscholar/modules/contrib/
git commit -a -m "Update Temporary Modules." || echo 'Nothing to commit.'
fi
git push origin $CI_BRANCH
echo -e "\033[1;36mFINISHED BUILDING $CI_BRANCH ON BITBUCKET\e[0m"

