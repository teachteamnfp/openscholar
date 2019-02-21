#!/usr/bin/env bash
# Quick codeship script

# pull down the acquia branch
mkdir -p ~/src/amazon/
git config --global user.email "openscholar@swap.lists.harvard.edu"
git config --global user.name "OpenScholar Auto Push Bot"

BUILD_ROOT='/home/rof/src/amazon'
DOCROOT='web';

if git show-ref -q --verify refs/tags/$CI_BRANCH 2>&1 > /dev/null; then
  # This is just a tag push
  # There's no need to build ever for tags
  # All we need to do it
  #export $BRANCH = $(git branch --contains tags/$CI_BRANCH | grep -s 'SCHOLAR-' | sed -n 2p)
  export TAG_COMMIT=$(git rev-list -n 1 $CI_BRANCH)
  git clone git@bitbucket.org:openscholar/deploysource.git
  cd deploysource
  export ROOT_COMMIT=$(git log --all --grep="git-subtree-split: $TAG_COMMIT" | grep "^commit" | sed "s/commit //" | head -n 1)
  if [ -z "$ROOT_COMMIT" ]; then
    exit 1
  fi
  git checkout $ROOT_COMMIT
  git tag $CI_BRANCH
  git push --tags
  exit 0
elif git ls-remote --heads git@bitbucket.org:openscholar/deploysource.git | grep -sw $CI_BRANCH 2>&1>/dev/null; then
  git clone -b $CI_BRANCH git@bitbucket.org:openscholar/deploysource.git  ~/src/amazon;
  cd ~/src/amazon
else
  git clone -b 8.x-1.x-dev git@bitbucket.org:openscholar/deploysource.git  ~/src/amazon;
  cd ~/src/amazon
  git checkout -b $CI_BRANCH;
fi

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
composer install --ignore-platform-reqs

cd ..

#remove install.php
rm -Rf web/install.php || true
#remove the ignore file to checkin drupal core
rm -f openscholar/.gitignore

find openscholar/web openscholar/vendor -name '.git' | xargs rm -rf
find openscholar/web openscholar/vendor -name '.gitignore' | xargs rm -rf

# Add New Files to repo and commit changes
git add $BUILD_ROOT/openscholar

git commit -a -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID"
#END BUILD PROCESS
else
git commit -a -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID" || git commit --amend -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID"
fi


git push origin $CI_BRANCH
echo -e "FINISHED BUILDING $CI_BRANCH ON BITBUCKET"
