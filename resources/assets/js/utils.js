let csrfToken;

/**
 * Returns the CSRF token.
 *
 * @throws {Error} - Raises an error if the csrf meta tag cannot be found or is empty
 * @returns {string} CSRF token
 */
export const getCSRFToken = () => {
  if (!csrfToken) {
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenElement) {
      throw new Error('Unable to find csrf-token meta element');
    }

    if (!csrfTokenElement.content) {
      throw new Error('Got empty csrf-token meta element');
    }

    csrfToken = csrfTokenElement.content;
  }

  return csrfToken;
};
