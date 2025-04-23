import { getCSRFToken } from './utils';
import { ready } from './ready';

ready(() => {
    // Add plus 1 voucher click handler to all plus 1 voucher buttons
    document.querySelectorAll('[data-arrive-action][data-arrive-user-id]').forEach((element) => {
        element.addEventListener('click', handleArriveClick);
    });
});

/**
 * @typedef {Object} EditArrivalResponse
 * @property {boolean} state - user state arrived
 */

/**
 * Send an async request to increase the number of vouchers issued.
 *
 * @param {number} userId - ID of the user whose arrival state is updated
 * @param {string} action - action ("reset" / "arrived")
 *
 * @returns {Promise<EditArrivalResponse>}
 */
const sendArrivalState = async (userId, action) => {
    const csrfToken = getCSRFToken();
    const data = new FormData();
    data.append('action', action);

    const response = await fetch(`/admin/user/${userId}/arrive`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: data,
    });
    if (!response.ok) {
        throw new Error(`Arrival state update response not okay`, response);
    }
    return await response.json();
};

/**
 * @param {MouseEvent} event
 */
const handleArriveClick = async (event) => {
    event.preventDefault();
    event.stopPropagation();

    /**
     * @type {HTMLButtonElement}
     */
    const element = event.currentTarget;
    const dataset = element.dataset;

    const action = String(dataset.arriveAction);
    const userId = Number(dataset.arriveUserId);

    if (Number.isInteger(userId) === false) {
        console.error('User ID is not an integer', userId);
        return;
    }
    if (action !== 'arrive' && action !== 'reset' && action !== '') {
        console.error('Invalid action', action);
        return;
    }

    // Block user from multiple inputs
    element.disabled = true;

    try {
        if (action) {
            const icons = {
                arrive: 'bi-house',
                reset: 'bi-arrow-counterclockwise'
            }
            const btnType = {
                arrive : 'btn-primary',
                reset: 'btn-danger'
            }
            const editArrivalState = await sendArrivalState(userId, action);
            const arrived = Boolean(editArrivalState.state);
            element.dataset.arriveAction = arrived ? '' : 'arrive'
            if (arrived){
                element.classList.replace(btnType.arrive, btnType.reset)
                element.firstElementChild.classList.replace(icons.arrive, icons.reset)
                element.dataset.modalShow = '1';
            } else {
                element.classList.replace(btnType.reset, btnType.arrive)
                element.firstElementChild.classList.replace(icons.reset, icons.arrive)
                element.dataset.modalShow = '';
            }
        } else {
            element.dataset.arriveAction = 'reset';
        }
    } catch (error) {
        console.error('Error during update arrived request', error);
    } finally {
        element.disabled = false;
    }
};
