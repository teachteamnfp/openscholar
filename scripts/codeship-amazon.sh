#!/usr/bin/env bash
# Quick codeship script

# pull down the acquia branch
mkdir -p ~/src/amazon/
git config --global user.email "openscholar@swap.lists.harvard.edu"
git config --global user.name "OpenScholar Auto Push Bot"

BUILD_ROOT='/home/rof/src/amazon'
DOCROOT='web';

# Build this branch and push it to Amazon
# Set up global configuration and install tools needed to build
composer global require drush/drush
mkdir -p ~/.drush
printf "disable_functions =\nmemory_limit = 256M\ndate.timezone = \"America/New_York\"" > ~/.drush/php.ini
export PATH="$HOME/.composer/vendor/bin:$PATH"
drush --version || exit 1

# Drush executable.
[[ $DRUSH && ${DRUSH-x} ]] || DRUSH=drush
cd $BUILD_ROOT

#Backup the make files
cp -f openscholar/composer.json /tmp/
cp -f openscholar/composer.lock /tmp/

git subtree pull -q -m "$CI_MESSAGE" --prefix=openscholar git://github.com/openscholar/openscholar.git $CI_BRANCH --squash

#Only build if no build has ever happened, or if the make files have changed
if [ ! -d openscholar/vendor ] || [ $FORCE_REBUILD == "1" ] || [ "$(cmp -b 'openscholar/composer.json' '/tmp/composer.json')" != "" ] || [ "$(cmp -b 'openscholar/composer.lock' '/tmp/dcomposer.lock')" != "" ] ]; then

# Chores.
echo "Rebuilding..."
cd openscholar

# Download composer components
composer install

cd ..

#remove install.php
rm -Rf web/install.php || true

# Add New Files to repo and commit changes
git add $BUILD_ROOT/openscholar

git commit -a -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID"
#END BUILD PROCESS
else

git push origin $CI_BRANCH
echo -e "\033[1;36mFINISHED BUILDING $CI_BRANCH ON BITBUCKET\e[0m"