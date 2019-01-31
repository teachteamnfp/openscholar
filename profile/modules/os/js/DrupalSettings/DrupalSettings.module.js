(function (ng) {

  let m = ng.module('DrupalSettings', []);

  /**
   * Makes Drupal Settings available in an Angular way
   * @constructor
   */
  let DrupalSettings = function () {
    let elem = document.querySelector('script[data-drupal-selector]');

    this.settings = JSON.parse(elem.innerHTML);
  };

  /**
   * Allow users to drill down into settings using 'dot' syntax.
   *
   * i.e. 'foo.bar' will return the value of { foo: { bar: val } }
   * @param obj
   * @param path
   * @returns {*}
   */
  function drillDown(obj, path) {
    let frags = path.split('.'),
      arg = frags.shift();

    if (frags.length === 0) {
      return obj[arg];
    }
    if (typeof obj[arg] !== 'undefined') {
      return drillDown(obj[arg], frags.join('.'));
    }
    return undefined;
  }

  /**
   * Check if a setting exists or not.
   * @param settingName
   * @returns {boolean}
   */
  DrupalSettings.prototype.hasSetting = function (settingName) {
    return drillDown(this.settings, settingName) === undefined;
  };

  /**
   * Fetch the setting value.
   * @param settingName
   * @returns {*}
   */
  DrupalSettings.prototype.fetchSetting = function (settingName) {
    let val = drillDown(this.settings, settingName);
    if (val === undefined) {
      throw 'Setting ' + settingName + ' not found';
    }
    return val;
  };

  m.service('drupalSettings', DrupalSettings);

})(angular);