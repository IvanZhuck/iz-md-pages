import InfoBlockToggle from './components/iz-md-info-block.js';

/**
 * Main application controller for plugin settings pages.
 */
class SettingsApp {
  /**
   * Initialize all settings page modules.
   *
   * @return {InfoBlockToggle[]}
   */
  static init () {
    const infoBlockElements = document.querySelectorAll('.iz-md-info-block');
    const instances = [];

    infoBlockElements.forEach((element) => {
      instances.push(new InfoBlockToggle(element));
    });

    return instances;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  SettingsApp.init();
});
