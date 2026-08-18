export default class MdMetaBox {
  /**
   * Initialize all meta box modules.
   */
  static init () {
    this.initManualToggle();
    this.initResetButton();
  }

  /**
   * Initialize visibility toggling for the manual Markdown content field.
   */
  static initManualToggle () {
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

  /**
   * Initialize reset button to restore default template.
   */
  static initResetButton () {
    const resetBtn = document.getElementById('iz-md-reset-default-btn');
    const manualTextarea = document.getElementById('iz_md_manual_content');

    if (!resetBtn || !manualTextarea) {
      return;
    }

    resetBtn.addEventListener('click', () => {
      const defaultTemplate = resetBtn.getAttribute('data-default-template') || '';
      const confirmMessage = resetBtn.getAttribute('data-confirm-message') ||
          'Are you sure you want to reset the content to the default template?';

      if (manualTextarea.value.trim() !== '' && manualTextarea.value !== defaultTemplate) {
        if (!window.confirm(confirmMessage)) {
          return;
        }
      }

      manualTextarea.value = defaultTemplate;
      manualTextarea.dispatchEvent(new Event('input', { bubbles: true }));
      manualTextarea.dispatchEvent(new Event('change', { bubbles: true }));
      manualTextarea.focus();
    });
  }
}
