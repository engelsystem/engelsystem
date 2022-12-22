/**
 * Formats a Date to HH:MM, e.g. 09:23 or 13:37
 *
 * @param {Date} date
 * @returns {string|undefined} Formatted time or undefined for non-Date
 */
export const formatTime = (date) => {
  if (!date instanceof Date) return;
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${hours}:${minutes}`;
};

/**
 * Formats a Date to YYYY-MM-DD, e.g. 2023-05-18
 *
 * @param {Date} date
 * @returns {string|undefined} Formatted date or undefined for non-Date
 */
export const formatDay = (date) => {
  if (!date instanceof Date) return;

  const year = String(date.getFullYear());
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
};
