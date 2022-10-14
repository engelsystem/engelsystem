import { getCSRFToken } from './utils';

/**
 * Handles +1 voucher clicks.
 */
document.addEventListener('click', async (event) => {
    const element = event.target;
    if (element.matches('[data-voucher-amount][data-voucher-user-id]')) {
        const dataset = element.dataset;

        if (
            typeof dataset.voucherAmount === 'undefined'
            || typeof dataset.voucherUserId === 'undefined'
        ) {
            console.log('voucher element missing data attribute');
            return;
        }

        const amount = parseInt(dataset.voucherAmount, 10);
        const userId = parseInt(dataset.voucherUserId, 10);

        let csrfToken;

        try {
            csrfToken = getCSRFToken();
        } catch (e) {
            console.log(e);
            return;
        }

        const data = new FormData();
        data.append('submit', 'true');
        data.append('vouchers', amount);

        let response;

        try {
            response = await fetch(`/users?action=edit_vouchers&user_id=${userId}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: data,
            });
        } catch (e) {
            console.log('error during voucher request', e);
        }

        if (!response.ok) {
            console.log(`voucher update failed`, response);
            return;
        }

        let responseData;

        try {
            responseData = await response.json();
        } catch (e) {
            console.log('error parsing voucher response', e);
            return;
        }

        element.parentNode.querySelector('[data-field="voucher-status"]')
            .textContent = `${responseData.issued} / ${responseData.eligible}`;
        element.dataset.voucherAmount = responseData.issued + 1;
    }
});
