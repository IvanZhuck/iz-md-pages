export default class MdMetaBox {
  /**
   * Initialize all meta box modules.
   */
  static init () {
    const manualCheckbox = document.getElementById('iz_md_manual_enabled');
    const manualContentWrapper = document.getElementById('iz-md-manual-content-wrapper') ||
        document.querySelector('.iz-md-manual-content-wrapper');

    if (!manualCheckbox || !manualContentWrapper) {
      return;
    }

    const toggleManualContent = () => {
      const isChecked = manualCheckbox.checked;
      if (isChecked) {
        manualContentWrapper.classList.remove('is-hidden');
        manualContentWrapper.style.display = '';
      } else {
        manualContentWrapper.classList.add('is-hidden');
        manualContentWrapper.style.display = 'none';
      }
    };

    manualCheckbox.addEventListener('change', toggleManualContent);
    toggleManualContent();
  }
}
