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
 * @property {string} arrival_date - user state arrival date
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

    const action = dataset.arriveAction || '';
    const userId = Number(dataset.arriveUserId);

    if (!Number.isInteger(userId)) {
        console.error('User ID is not an integer', userId);
        return;
    }
    switch (action) {
        case 'arrive':
        case 'reset':
        case '':
            break;
        default:
            console.error('Invalid action', action);
            return;
    }

    // Block user from multiple inputs
    element.disabled = true;

    try {
        if (action) {
            const editArrivalState = await sendArrivalState(userId, action);
            const arrived = Boolean(editArrivalState.state);
            const arrivalDate = String(editArrivalState.arrival_date);
            const tableRow = element.parentElement.parentElement;
            const arrivalDateElement = tableRow.querySelector('.column_rendered_arrival_date');
            const userNameElement = tableRow.querySelector('.column_name').firstElementChild;
            const arriveIconElement = tableRow.querySelector('.column_arrived').firstElementChild;

            element.dataset.arriveAction = arrived ? '' : 'arrive';
            arrivalDateElement.innerHTML = arrivalDate;

            if (arrived) {
                element.dataset.modalShow = '1';
                handleActionButton(true, element);
                handleArriveIcon(true, arriveIconElement);
                // element.classList.replace(btnType.arrive, btnType.reset);
                // element.firstElementChild.classList.replace(icons.arrive, icons.reset);
                userNameElement.classList.remove('text-muted');
            } else {
                element.dataset.modalShow = '';
                handleActionButton(false, element);
                handleArriveIcon(true, arriveIconElement);
                // element.classList.replace(btnType.reset, btnType.arrive);
                // element.firstElementChild.classList.replace(icons.reset, icons.arrive);
                userNameElement.classList.add('text-muted');
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

const handleActionButton = (arrived, buttonElement) => {
    const iconElement = buttonElement.firstElementChild;
    const icons = {
        arrive: 'bi-house',
        reset: 'bi-arrow-counterclockwise'
    };
    const btnType = {
        arrive : 'btn-primary',
        reset: 'btn-danger'
    };
    if (arrived) {
        buttonElement.classList.replace(btnType.arrive, btnType.reset);
        iconElement.classList.replace(icons.arrive, icons.reset);
        buttonElement.title = buttonElement.dataset.resetTitle;
    } else {
        buttonElement.classList.replace(btnType.reset, btnType.arrive);
        iconElement.classList.replace(icons.reset, icons.arrive);
        buttonElement.title = buttonElement.dataset.arriveTitle;
    }
}

const handleArriveIcon = (arrived, element) => {
    const iconElement = element.firstElementChild;
    const icons = {
        arrived: 'bi-check-lg',
        away: 'bi-x-lg'
    };
    const textType = {
        arrived : 'text-success',
        away: 'text-danger'
    };
    if (arrived) {
        element.classList.replace(textType.arrived, textType.away);
        iconElement.classList.replace(icons.arrived, icons.away);
    } else {
        element.classList.replace(textType.away, textType.arrived);
        iconElement.classList.replace(icons.away, icons.arrived);
}}
