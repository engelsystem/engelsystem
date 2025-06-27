import { ready } from './ready';

ready(() => {
  // Add plus 1 voucher click handler to all plus 1 voucher buttons
  document.querySelectorAll('form_user_angel_type_add_user_id, select').forEach((element) => {
    const innerDiv = element.choices.containerOuter.element;
    if (innerDiv) {
      innerDiv.focus(); // only works if div is focusable
    }
  });
});
