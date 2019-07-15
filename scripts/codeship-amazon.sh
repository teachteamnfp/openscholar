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

# Basic setups
phpenv local 7.2
php -v || exit 1

# Build this branch and push it to Amazon
# Set up global configuration and install tools needed to build
export PATH="$HOME/.composer/vendor/bin:$PATH"

cd $BUILD_ROOT

#Backup the make files
cp -f openscholar/composer.json /tmp/
cp -f openscholar/composer.lock /tmp/
cd openscholar/profile/themes
cp -rf . /tmp/

cd $BUILD_ROOT

git remote update
git subtree pull -q -m "$CI_MESSAGE" --prefix=openscholar git://github.com/openscholar/openscholar.git $CI_BRANCH --squash

cd openscholar/profile/themes

SHOULD_REBUILD_SCSS=0
for theme in * ; do
  [[ ! -e "$theme/scss" ]] && [[ ! -e "/tmp/$theme/scss" ]] && continue;

  # If scss directory is present in one, but not in other, that means scss needs
  # to be rebuilt.
  if [[ -e "$theme/scss" ]] && [[ ! -e "/tmp/$theme/scss" ]]; then
    SHOULD_REBUILD_SCSS=1
    break
  fi
  if [[ ! -e "$theme/scss" ]] && [[ -e "/tmp/$theme/scss" ]]; then
    SHOULD_REBUILD_SCSS=1
    break
  fi

  diff -r "$theme/scss" "/tmp/$theme/scss" >> "$BUILD_ROOT/scss.diff";
done

if [[ -e "$BUILD_ROOT/scss.diff" ]] && [[ "$(cat ${BUILD_ROOT}/scss.diff)" != "" ]]; then
  SHOULD_REBUILD_SCSS=1
fi

cd ${BUILD_ROOT}

#Only build if no build has ever happened, or if the make files have changed
if [[ $FORCE_REBUILD == "1" ]] || [[ "$(cmp -b 'openscholar/composer.json' '/tmp/composer.json')" != "" ]] || [[ "$(cmp -b 'openscholar/composer.lock' '/tmp/composer.lock')" != "" ]] || [[ ${SHOULD_REBUILD_SCSS} -eq 1 ]]; then

  # Chores.
  echo "Rebuilding..."
  cd openscholar

  # Directories that track via .git need to be removed before they are updated see https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md
  rm -rf web/modules/contrib/purl || true
  rm -rf vendor/drupal/coder || true
  rm -rf web/modules/contrib/views_ical || true
  rm -rf web/modules/contrib/bibcite || true
  rm -rf vendor/behat/mink || true
  rm -rf web/modules/contrib/repec || true

  # Download composer components
  composer install --ignore-platform-reqs --no-interaction --prefer-dist --no-dev || exit 1

  # Do not use the node_modules symlink, and reinstall node modules
  rm -rf node_modules
  npm install || exit 1

  # Build CSS
  cd profile/themes
  ./../../node_modules/.bin/gulp sass || exit 1

  cd ../..

  cd profile/libraries/os-toolbar
  ./../../../node_modules/.bin/gulp sass || exit 1

  cd ../../../..

  #remove install.php
  rm -Rf web/install.php || true
  #remove the ignore file to checkin drupal core
  rm -f openscholar/.gitignore

  find openscholar/web openscholar/vendor openscholar/node_modules -name '.git' | xargs rm -rf
  find openscholar/web openscholar/vendor openscholar/node_modules -name '.gitignore' | xargs rm -rf

  # Add New Files to repo and commit changes
  git add $BUILD_ROOT/openscholar

  git commit -a -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID"
else
  #END BUILD PROCESS
  git commit -a -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID" || git commit --amend -m "$CI_MESSAGE" -m "" -m "git-subtree-split: $CI_COMMIT_ID"
fi

git push origin $CI_BRANCH
echo -e "FINISHED BUILDING $CI_BRANCH ON BITBUCKET"
