import { getCSRFToken } from './utils';
import { ready } from './ready';

ready(() => {
  // Add plus 1 voucher click handler to all plus 1 voucher buttons
  document.querySelectorAll('[data-voucher-amount][data-voucher-user-id]').forEach((element) => {
    element.addEventListener('click', handlePlus1VoucherClick);
  });
});

/**
 * @typedef {Object} EditVoucherResponse
 * @property {number} eligible
 * @property {number} issued
 * @property {number} total - Total number of issued vouchers
 */

/**
 * Send an async request to increase the number of vouchers issued.
 *
 * @param {number} userId - ID of the user whose voucher amount is to be updated
 * @param {number} amount - Voucher amount to set
 *
 * @returns {Promise<EditVoucherResponse>}
 */
const sendEditVoucherRequest = async (userId, amount) => {
  const csrfToken = getCSRFToken();

  const data = new FormData();
  data.append('got_voucher', amount);

  const response = await fetch(`/admin/user/${userId}/voucher`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: data,
  });

  if (!response.ok) {
    throw new Error(`Voucher update response not okay`, response);
  }

  return await response.json();
};

/**
 * @param {MouseEvent} event
 */
const handlePlus1VoucherClick = async (event) => {
  event.preventDefault();
  event.stopPropagation();

  /**
   * @type {HTMLButtonElement}
   */
  const element = event.target;
  const dataset = element.dataset;

  const amount = Number(dataset.voucherAmount);
  const userId = Number(dataset.voucherUserId);

  if (Number.isInteger(userId) === false) {
    console.error('User ID is not an integer', userId);
    return;
  }

  if (Number.isInteger(amount) === false) {
    console.error('Voucher amount is not an integer', amount);
    return;
  }

  // Block user from multiple inputs
  element.disabled = true;

  try {
    const editVoucherResponse = await sendEditVoucherRequest(userId, amount);

    // Update user voucher numbers
    element.parentNode.querySelector(
      '[data-field="voucher-status"]'
    ).textContent = `${editVoucherResponse.issued} / ${editVoucherResponse.eligible}`;
    element.dataset.voucherAmount = editVoucherResponse.issued + 1;

    // Update total voucher count
    const totalElement = document.getElementById('voucher-count');

    if (totalElement !== null) {
      totalElement.innerText = editVoucherResponse.total;
    }
  } catch (error) {
    console.error('Error during update voucher request', error);
  } finally {
    element.disabled = false;
  }
};
