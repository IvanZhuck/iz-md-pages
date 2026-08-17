/**
 * Handles collapsible state for the template placeholders reference block.
 */
class PlaceholdersReferenceToggle {
  static STORAGE_KEY = 'iz_md_placeholders_reference_collapsed';

  /**
   * @param {HTMLElement} element Root container element.
   */
  constructor (element) {
    this.container = element;
    this.header = element.querySelector('.iz-md-placeholders-header');
    this.toggleBtn = element.querySelector('.iz-md-placeholders-toggle');
    this.toggleText = element.querySelector('.iz-md-placeholders-toggle-text');

    this.textExpand = this.toggleText?.getAttribute('data-text-expand') || 'Expand';
    this.textCollapse = this.toggleText?.getAttribute('data-text-collapse') || 'Collapse';

    this.init();
  }

  /**
   * Initialize state and event listeners.
   */
  init () {
    this.restoreState();
    this.bindEvents();
  }

  /**
   * Restore collapsed state from localStorage.
   */
  restoreState () {
    try {
      const isCollapsed = localStorage.getItem(PlaceholdersReferenceToggle.STORAGE_KEY) === 'true';
      if (isCollapsed) {
        this.collapse();
      } else {
        this.expand();
      }
    } catch {
      // localStorage access may be restricted
    }
  }

  /**
   * Attach DOM event listeners.
   */
  bindEvents () {
    if (this.header) {
      this.header.addEventListener('click', () => this.toggle());
      this.header.addEventListener('keydown', (event) => this.handleKeyDown(event));
    } else if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggle());
    }
  }

  /**
   * Handle keyboard navigation (Enter / Space keys).
   *
   * @param {KeyboardEvent} event
   */
  handleKeyDown (event) {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      this.toggle();
    }
  }

  /**
   * Toggle collapsed/expanded state and save to localStorage.
   */
  toggle () {
    const isCurrentlyCollapsed = this.container.classList.contains('is-collapsed');
    if (isCurrentlyCollapsed) {
      this.expand();
      this.persistState(false);
    } else {
      this.collapse();
      this.persistState(true);
    }
  }

  /**
   * Collapse the reference block.
   */
  collapse () {
    this.container.classList.add('is-collapsed');
    if (this.header) {
      this.header.setAttribute('aria-expanded', 'false');
    }
    if (this.toggleText) {
      this.toggleText.textContent = this.textExpand;
    }
  }

  /**
   * Expand the reference block.
   */
  expand () {
    this.container.classList.remove('is-collapsed');
    if (this.header) {
      this.header.setAttribute('aria-expanded', 'true');
    }
    if (this.toggleText) {
      this.toggleText.textContent = this.textCollapse;
    }
  }

  /**
   * Persist state in localStorage.
   *
   * @param {boolean} isCollapsed
   */
  persistState (isCollapsed) {
    try {
      localStorage.setItem(PlaceholdersReferenceToggle.STORAGE_KEY, String(isCollapsed));
    } catch {
      // Storage quota or private browsing mode
    }
  }
}

/**
 * Main application controller for plugin settings pages.
 */
class SettingsApp {
  /**
   * Initialize all settings page modules.
   */
  static init () {
    const referenceElement = document.getElementById('iz-md-placeholders-reference');
    if (referenceElement) {
      return new PlaceholdersReferenceToggle(referenceElement);
    }
    return null;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  SettingsApp.init();
});
