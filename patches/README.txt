Patches
=======

Core
====

Patch - patches/11840-custom-theme-caching.patch
Project issue - https://github.com/openscholar/openscholar/issues/11840
Description - Custom theme installed for a vsite should not affect performance
in other. Installing a custom theme and setting it as default was doing some
unnecessary cleanups, like rebuilding routes, invalidating rendered cache tag,
and slowing down the other vsite. The patch disables those unwanted cleanups.


Contrib
=======

Group
Issue: https://www.drupal.org/node/2774827
Comment: https://www.drupal.org/node/2774827#comment-11907035
Patch: https://www.drupal.org/files/issues/group_add-node-group-tokens-2774827-24.patch
Get a token of a node's parent group to create a pathauto pattern


Libraries
=========
