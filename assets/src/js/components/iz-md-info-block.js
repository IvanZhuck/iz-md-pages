/**
 * Handles collapsible state for info blocks / reference blocks.
 */
export default class InfoBlockToggle {
  static DEFAULT_STORAGE_KEY = 'iz_md_info_block_collapsed';

  /**
   * @param {HTMLElement} element Root container element.
   * @param {string} [storageKey] Optional custom localStorage key.
   */
  constructor (element, storageKey) {
    this.container = element;
    this.header = element.querySelector('.iz-md-info-block-header');
    this.toggleBtn = element.querySelector('.iz-md-info-block-toggle');
    this.toggleText = element.querySelector('.iz-md-info-block-toggle-text');

    this.textExpand = this.toggleText?.getAttribute('data-text-expand') || 'Expand';
    this.textCollapse = this.toggleText?.getAttribute('data-text-collapse') || 'Collapse';

    this.storageKey = storageKey ||
      this.container.getAttribute('data-storage-key') ||
      (this.container.id ? `iz_md_${this.container.id}_collapsed` : InfoBlockToggle.DEFAULT_STORAGE_KEY);

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
      const isCollapsed = localStorage.getItem(this.storageKey) === 'true';
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
      localStorage.setItem(this.storageKey, String(isCollapsed));
    } catch {
      // Storage quota or private browsing mode
    }
  }
}
