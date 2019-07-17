/**
 * EntityDatabaseService
 *
 * Provides an interface for communicating CRUD operations on entities to the server.
 */
(function () {
  var m = angular.module('EntityService', 'DrupalSettings', 'UrlGenerator');

  m.provider('EntityDatabaseService', function () {
    var configs = {};
    var provider = this;
    var ignore = {
      files: ['preview', 'url', 'size', 'changed', 'mimetype'],
      node: [],
      vocabulary: [],
      taxonomy: {},
    };
    let format = '?_format=json';
    /**
     * Adds a new configuration that the entity database should manage.
     * @param name - An object safe string
     * @param config - An object that describes the configuration
     *  {
     *    entityType: the type of entity to operate on
     *    params: object of parameters to filter by {
     *      string: matches a value exactly
     *      array: match any item in the array
     *    },
     *    fields: {
     *      additionalfields: to pass to http requests
     *    }
     *  }
     */
    this.AddConfig = function (name, config) {
      if (!(/[a-zA-Z0-9_]+/).test(name)) {
        throw new Exception(name + ' is not an acceptable config name.');
      }

      if (config.entityType == undefined) {
        throw new Exception('Config ' + name + ' cannot have an undefined entity type.');
      }

      if (configs[name] == undefined) {
        configs[name] = config;
      }
    };

    this.$get = ['$http', '$q', '$timeout', '$indexedDB', 'drupalSettings', 'urlGenerator', function ($http, $q, $t, $idb, settings, urlGenerator) {
      var Service = {};
      var httpConfig = {
        params: {}
      };

      var databaseParams = {};

      let vsite = false;
      if (settings.hasSetting('spaces.id')) {
        vsite = httpConfig.params.vsite = databaseParams.vsite = settings.fetchSetting('spaces.id');
      }
      var restPath = urlGenerator(settings.fetchSetting('paths.api'));

      /**
       * Check for updates to our existing databases.
       */
      var checked = false;
      Service.LoadDatabase = function () {
        if (checked) {
          return;
        }
        checked = true;
        $idb.openStore('entities', function (store) {
          var results;
          // if we're in a vsite, only get a subset of the database
          // we really don't need to loop through everything
          if (vsite) {
            var query = store.query()
              .$index('vsite')
              .$eq(vsite);

            results = store.eachWhere(query);
          }
          else {
            results = store.getAll();
          }

          results.then(function (rows) {
            var emptyParamCount = 0;
            if (vsite) {
              emptyParamCount = 1;
            }
            var args = {}
            for (var i = 0; i < rows.length; i++) {
              var entityType = rows[i].entityType;

              if (Object.keys(rows[i].params).length == emptyParamCount) {
                var configs = args[entityType].configs || [];
                args[entityType] = ['noop', entityType, rows[i].data, rows[i].lastUpdated];
                args[entityType].configs = configs;
              }
              else {
                if (args[entityType] == undefined) {
                  args[entityType] = [entityType];
                }
              }
              // figure out the name if one is not provided
              var name = rows[i].name;
              if (!name) {
                for (var k in configs) {
                  var params = angular.copy(configs[k].params) || {};
                  if (vsite) {
                    params.vsite = vsite;
                  }
                  if (angular.equals(params, rows[i].params)) {
                    name = k;
                    break;
                  }
                }
              }
              args[entityType].configs = args[entityType].configs || [];
              args[entityType].configs.push({name: name, params: rows[i].params});
            }

            for (var type in args) {
              Service[type] = new (Function.prototype.bind.apply(EntityTypeHandler, args[type]));
              for (i = 0; i < args[type].configs.length; i++) {
                var a = args[type].configs[i];
                Service[type].addConfig(a.name, a.params);
              }
            }
          });
        }).then(angular.noOp, function () {
          // Backup if indexedDB doesn't work for whatever reason.
          for (var name in configs) {
            var entityType = configs[name].entityType;
            if (Service[entityType] == undefined) {
              Service[entityType] = new EntityTypeHandler();
            }

            Service[entityType].addConfig(name, configs[name]);
          }
        });
      };

      return Service;

      /**
       * Handles all server communication and local database interactions for a single entity type
       *
       * @param entityType
       * @param entities - optional - a list of entities to pass from the local database
       * @param timestamp - optional - the last time those entities were fetched
       * @constructor
       */
      function EntityTypeHandler (entityType, entities, timestamp) {
        var self = this;
        var baseUrl = restPath + '/' + entityType;
        this.entities = [];
        if (angular.isArray(entities)) {
          this.entities = entities;
        }
        console.log(entities);
        var configKeys = [],
          loaded = 0,
          lastUpdated = 0;

        /**
         * Initialization for given entity type.
         */
        // There were no entities provided, we're getting them for the first time.
        if (this.entities.length == 0) {
          $http.get(baseUrl, httpConfig)
            .success(fetchSuccess)
            .error(errorFunc);
        }
        // The constructor was provided entities. We only need updates.
        else {
          fetchUpdates();
        }

        /**
         * Registers a new config, that can be refered to with EntityDatabaseService.entityType.configName
         * @param name
         * @param config
         */
        this.addConfig = function (name, config) {
          this[name] = new EntitySubset(this, config);
        };

        /**
         * @returns {number} - Total percentage of completed loading operation
         */
        this.LoadPercent = function () {
          return loaded;
        };

        /**
         * @returns {boolean} - Whether loading operation has completed or not.
         * @constructor
         */
        this.Loaded = function () {
          return loaded == 100;
        };

        /**
         * HTTP response handler
         * Recursive
         * @param resp
         * @param status
         * @param headers
         * @param config
         */
        function fetchSuccess (resp, status, headers, config) {
          self.entities = self.entities.concat(resp.data);

          if (resp.next) {
            var max = Math.ceil(resp.count / resp.data.length),
              curr = resp.next.href.match(/page=([\d]+)/)[1];
            loaded = Math.round(((curr - 1) / max) * 100);
            $http.get(resp.next.href).success(fetchSuccess);
          }
          else {
            loaded = 100;
            self.updateDatabase();
          }
        }

        /**
         * HTTP response error handler
         */
        var errorAttempts;

        function errorFunc () {
          errorAttempts++;
          if (errorAttempts < 3) {
            $http.get(restPath + '/' + entityType, httpConfig).
              success(fetchSuccess).
              error(errorFunc);
          }
          else {
            console.log('Error getting files. Aborting after 3 attempts.');
          }
        }

        /**
         * Fetches first/next 50 updates from the server, then queues up the next request if necessary.
         * @param nextUrl
         */
        function fetchUpdates (nextUrl) {
          var url = nextUrl || baseUrl + '/updates/' + timestamp;

          $http.get(url, httpConfig).then(function (resp) {
            var isUpdates = typeof resp.data.updatesAsOf != 'undefined';
            if (resp.data.next) {
              fetchUpdates(resp.data.next.href);
            }

            var entities = resp.data.data;
            for (var i = 0; i < entities.length; i++) {
              for (j = 0; j < self.entities.length; j++) {
                if (self.entities[j].id == entities[i]) {
                  self.entities[j] = entities[i];
                  continue;
                }
              }
            }
          });
        }

        /**
         * Adds additional fields to the fetch query so a subset can be requested directly from the server.
         * @param fields
         * @returns {*}
         */
        this.fetchSubset = function (fields) {
          var defer = $q.defer();
          for (var k in fields) {
            httpConfig.params[k] = fields[k];
          }
          var output = [];
          $http.get(baseUrl, httpConfig).success(fetchSubsetSuccess).error(function (resp) {
            console.log('Error fetching subset.');
          });

          return defer.promise;

          function fetchSubsetSuccess (resp, status, headers, config) {
            output = output.concat(resp.data);

            if (resp.next) {
              var max = Math.ceil(resp.count / resp.data.length),
                curr = resp.next.href.match(/page=([\d]+)/)[1];
              loaded = Math.round(((curr - 1) / max) * 100);
              defer.notify(loaded);
              $http.get(resp.next.href).success(fetchSubsetSuccess);
            }
            else {
              defer.resolve(output);
            }
          }
        }

        /**
         * Find the key of the given entity id in the master array
         * @param id
         * @returns {number}
         */
        function findEntityKeyById (id) {
          if (!angular.isNumber(id)) {
            return -1;
          }
          for (var i = 0; i < self.entities.length; i++) {
            if (self.entities[i].id == id) {
              return i;
            }
          }

          return -1;
        }

        /**
         * Adds an entity to the list of all entities, taking into account whether it exists or not prior
         * @param entity
         */
        function addOrUpdate (entity) {
          var i = findEntityKeyById(entity.id);
          if (i != -1) {
            self.entities[i] = entity;
          }
          else {
            self.entities.push(entity);
          }
        }

        /**
         * Matches an entity against the defined subsets
         * @param entity
         */
        function checkEntityAgainstConfigs (entity) {
          for (var i = 0; i < configKeys.length; i++) {
            var key = configKeys[i];
            self[key].match(entity);
          }
        }

        /**
         * Allows for registering an entity to the database that was created outside of this service
         * i.e. file uploads
         * @param entity
         */
        this.register = function (entity) {
          addOrUpdate(entity);
        };

        /**
         * Uploads a brand new entity to the server
         * @param entity
         */
        this.create = function (entity, extra) {
          if (findEntityKeyById(entity.id) > -1) {
            throw "Entity " + entity.id + " of type " + entityType + " already exists, and cannot be created again.";
          }

          var defer = $q.defer();
          $http.post(baseUrl, entity).then(function (resp) {
              var entity = resp.data;
              self.entities.push(entity);
              defer.resolve(entity);
            },
            function (resp) {
              console.log(resp.data);
              defer.reject("Failed to create entity. Check error logs.");
            });

          return defer.promise;
        };

        /**
         * Uploads changes to an entity to the server.
         * @param entity - The full entity to be uploaded
         *
         * @return A Promise representing the operation
         */
        this.edit = function (entity) {
          var ignoredFields = ignore[entityType],
            originalKey = findEntityKeyById(entity.id),
            original = entities[originalKey],
            data = getDiff(entity, original, ignoredFields);

          if (data.length) {
            delete data.length;

            var config = angular.copy(httpConfig);
            config.headers = {};
            config.headers['If-Unmodified-Since'] = (new Date(original.changed * 1000)).toString().replace(/ \([^)]*\)/, '');

            var defer = $q.defer();
            $http.patch(baseUrl + '/' + entity.id, data, config).then(function (resp) {
              var updated = resp.data;
              entities[originalKey] = updated;
              self.updateDatabase();
              defer.resolve(entity);
            }, function (resp) {
              switch (resp.status) {
                case 409:
                case 410:
                  console.log("Cache invalid. Reloading.");
                  defer.reject("Cache invalid");
                  checked = false;
                  this.CheckForUpdates();
                  break;
                default:
                  console.log(resp.data);
                  defer.reject("Failed to update entity. Check error logs.");
              }
            });

            return defer.promise;
          }
        };

        /**
         * Deletes an entity from the server.
         * @param entity - The full entity to delete
         */
        this.delete = function (entity) {

          var config = angular.copy(httpConfig),
            originalKey = findEntityKeyById(entity.id),
            original = entities[originalKey];

          config.headers = config.headers || {};
          config.headers['If-Unmodified-Since'] = (new Date(original.changed * 1000)).toString().replace(/ \([^)]*\)/, '');

          var defer = $q.defer();
          $http.delete(baseUrl + '/' + entity.id, config).then(function (resp) {
            self.entities.splice(key, 1);
            self.updateDatabase();
            $q.resolve("Success");
          }, function (resp) {
            console.log(resp.data);
            $q.reject("Failed to delete entity " + entity.id);
          });

          var key = findEntityKeyById(entity.id);
          return defer.promise;
        };

        /**
         * Update the database and all subgroup databases
         * WARNING: This reads from the EntityTypeHandler's entities array. Any changes must be made to it BEFORE
         * calling this function or they will NOT be saved.
         */
        this.updateDatabase = function () {
          // try to figure out a name for this set so we can initialize it in later runs
          var name;
          for (var k in configs) {
            var clone = angular.copy(configs[k]);
            clone.params = clone.params || {};
            if (databaseParams.vsite) {
              clone.params.vsite = databaseParams.vsite;
            }
            if (angular.equals(configs[k].params, databaseParams)) {
              name = k;
              break;
            }
          }
          $idb.openStore('entities', function (store) {
            var key = entityType + ":" + JSON.stringify(databaseParams),
              cacheObject = {
                data: self.entities,
                key: key,
                name: name,
                entityType: entityType,
                idProperty: "id",
                lastUpdated: lastUpdated,
                params: databaseParams,
                matches: (function(entity, entityType) {
                  if (entityType != this.entityType) {
                    return false;
                  }
                  return testEntity.call(this, entity);
                }).toString()
              };
            store.upsert(cacheObject);
            for (var i = 0; i < configKeys.length; i++) {
              self[configKeys[i]].updateDatabase(configKeys[i], store, lastUpdated);
            }
          });
        }
      }

      /**
       * Manages a subset of entities, based on parameters given.
       * Nearly every interaction should go through this.
       * @param typeHandler
       * @param config
       * @constructor
       */
      function EntitySubset (typeHandler, config) {
        config.fields = config.fields || {};
        config.params = config.params || {};

        var subset = [];

        /**
         * Matches a single entity against the params given in config
         * @param entity
         * @returns {boolean}
         */
        function match (entity) {
          var params = config.params,
            type = config.entityType;

          // if this cache isn't for private files, and the entity is private, drop it.
          //if (!params.private && entity.schema == 'private') {
          //  return false;
          //}

          for (var k in params) {
            if ((k == 'entity_type' || k == 'bundle') && typeof entity.bundles == 'object') {
              // this thing refers to other entities, like vocabs do. It can accept multiple types of entity type / file combinations

              if (entity.bundles[params.entity_type]) {
                var found = false;
                for (var l in entity.bundles[params.entity_type]) {
                  if (entity.bundles[params.entity_type][l] == params.bundle) {
                    found = true;
                    break;
                  }
                }
                if (!found) {
                  return false;
                }
              }
              else {
                // this does not accept the entity type we are looking for
                return false;
              }
            }
            // allow for a property on the entity to be a container that we only need to match one of to pass
            else if (entity[k] instanceof Object && (typeof params[k] == 'string' || typeof params[k] == 'number')) {
              var found = false;
              for (var l in entity[k]) {
                if (entity[k][l] == params[k]) {
                  found = true;
                  break;
                }
              }
              if (!found) {
                return false;
              }
            }
            else if (entity[k] == undefined) {
              // this is not something that comes returned on the object
              // try other things
              switch (k) {
                case 'vsite':
                  if (!settings.hasSetting('spaces.id') || params[k] != settings.fetchSetting('spaces.id')) {
                    return false;
                  }
                  break;
                case 'private':
                {
                  if (params[k] == 'only' && entity.schema != 'private') {
                    return false;
                  }
                }
              }
            }
            else if (entity[k] != params[k]) {
              return false;
            }
          }
          return true;
        }

        /**
         * Returns a list of all entities that match the config params given in the constructor.
         * @returns {Array.<T>}
         * @constructor
         */
        var fetchPromise;
        var loadPercent = 0;
        this.Entities = function () {
          subset = typeHandler.entities.filter(match);
          if (subset.length) {
            return subset;
          }

          if (typeHandler.Loaded()) {
            if (!fetchPromise) {
              fetchPromise = typeHandler.fetchSubset(config.fields).then(function (resultSet) {
                console.log(resultSet);
                loadPercent = 100;
                subset = resultSet;
              }, angular.noOp, function (percent) {
                console.log(percent);
                loadPercent = percent;
              });
            }
          }
          return [];
        };

        /**
         * Updates the database for this specific parameter set if the config requires it.
         * @param store - indexedDB store
         * @param lastUpdated
         */
        this.updateDatabase = function (name, store, lastUpdated) {
          if (config.database) {
            var cacheObject = {
              data: this.Entities(),
              name: name,
              entityType: config.entityType,
              idProperty: "id",
              lastUpdated: lastUpdated,
              params: config.params,
            };
            store.upsert(cacheObject);
          }
        };

        /**
         * Pass through so special fields can be added to the request if necessary
         */
        this.create = function (entity) {
          typeHandler.create(entity, config.fields);
        };

        /**
         * Pass through so special fields can be added to the request if necessary
         */
        this.edit = function (entity) {
          typeHandler.edit(entity, config.fields);
        };

        /**
         * See @create
         */
        this.delete = function (entity) {
          typeHandler.delete(entity, config.fields);
        };

        /**
         * Load percentage
         */
        this.LoadPercent = function () {
          if (fetchPromise) {
            return loadPercent;
          }
          return typeHandler.LoadPercent();
        };

        /**
         * Is loading finished?
         */
        this.Loaded = function () {
          if (fetchPromise) {
            return loadPercent == 100;
          }
          return typeHandler.Loaded();
        }
      }
    }];


  });

  /**
   * Kick off the update process
   */
  m.run(['EntityDatabaseService', function (eDB) {
    eDB.LoadDatabase();
  }]);

  /**
   * Define the entity database.
   */
  m.config(['$indexedDBProvider', function ($idbp) {
    $idbp
      .connection('EntityService')
      .upgradeDatabase(2, function (event, db, tx) {
        var store = db.createObjectStore('entities', { keyPath: "key" });
        store.createIndex('key_idx', 'key', {unique: true});
      })
      .upgradeDatabase(3, function (event, db, tx) {
        var store = tx.objectStore('entities');
        store.createIndex('vsite', 'vsite', { unique: false });
      })
      .upgradeDatabase(4, function (event, db, tx) {
        var store = tx.objectStore('entities');
        store.deleteIndex('vsite');
        store.createIndex('vsite', 'params.vsite', { unique: false });
      });
  }]);

  /**
   * Returns only the values of an entity that have been changed, and the number of properties that are different.
   * @param oEntity - the original entitiy
   * @param nEntity - the changed entity
   * @param ignore - the fields on the entity that should be ignored
   * @returns {{}}
   */
  function getDiff(oEntity, nEntity, ignore) {
    var diff = {},
      numProps = 0;

    // Skip the property "$$hashKey" that's been generated by angular core
    // code - It's not relevant data thus should be skipped.
    ignore.push('$$hashKey');

    for (var k in oEntity) {
      if (ignore.indexOf(k) == -1 && !compareProps(oEntity[k], nEntity[k])) {
        diff[k] = nEntity[k];
        numProps++;
      }
    }
    diff.length = numProps;

    return diff;
  }

  /**
   * Compares two properties by value.
   * If property is an array or object, recurses into them.
   * @param prop1
   * @param prop2
   * @returns {boolean}
   */
  function compareProps(prop1, prop2) {
    if (typeof prop1 == 'object') {
      if (prop1 instanceof Array && prop2 instanceof Array) {
        return arrayEquals(prop1, prop2);
      }
      else if (prop1 == null && prop2 == null) {
        return true;
      }
      // rest apis return 'null' instead of an empty array
      // some widgets convert that into an empty array, and thus we need to check here
      else if (prop1 == null && prop2 instanceof Array && prop2.length == 0) {
        return true;
      }
      else if (prop2 == null && prop1 instanceof Array && prop1.length == 0) {
        return true;
      }
      // if neither property is an array, return false as usual
      else if (prop1 == null || prop2 == null) {
        return false;
      }
      else {
        return objectEquals(prop1, prop2);
      }
    }
    else if (typeof(prop1) == typeof(prop2)) {
      return prop1 == prop2;
    }
    // if one property is undefined, its usually a meta property the client uses
    // it shouldn't be used at all
    else if (typeof(prop1) == 'undefined' || typeof(prop2) == 'undefined') {
      return true;
    }
    else {
      return prop1.toString() == prop2.toString();
    }
  }

  /**
   * Recursively compares 2 arrays by value
   * @param arr1
   * @param arr2
   * @returns {boolean}
   */
  function arrayEquals(arr1, arr2) {
    if (arr1.length != arr2.length) {
      return false;
    }
    else {
      var diff = false;
      for (var i = 0; i < arr1.length; i++) {
        diff = diff || compareProps(arr1[i], arr2[i]);
      }

      return diff;
    }
  }

  /**
   * Recursively compares 2 objects by value
   * @param obj1
   * @param obj2
   * @returns {boolean}
   */
  function objectEquals(obj1, obj2) {
    var keys1 = objectKeys(obj1),
      keys2 = objectKeys(obj2),
      diff = false;

    if (arrayEquals(keys1, keys2)) {
      for (var k in obj1) {
        diff = diff || compareProps(obj1[k], obj2[k]);
      }

      return diff;
    }
    // objects have different keys
    return false;
  }

  /**
   * Returns an array of all keys on the object
   * @param obj
   * @returns {Array}
   */
  function objectKeys(obj) {
    var keys = [];
    for (var k in obj) {
      keys.push(k);
    }
    return keys;
  }


})();