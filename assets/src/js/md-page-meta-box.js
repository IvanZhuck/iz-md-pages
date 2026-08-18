import MdMetaBox from './components/md-meta-box';

/**
 * Controller for the MD page meta box.
 */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    MdMetaBox.init();
  });
} else {
  MdMetaBox.init();
}
