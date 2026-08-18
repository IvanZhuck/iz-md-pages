import InfoBlockToggle from './components/iz-md-info-block.js';

/**
 * Main application controller for plugin settings pages.
 */
class SettingsApp {
  /**
   * Initialize all settings page modules.
   */
  static init () {
    const infoBlockElements = document.querySelectorAll('.iz-md-info-block');

    infoBlockElements.forEach((element) => {
      new InfoBlockToggle(element);
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  SettingsApp.init();
});
