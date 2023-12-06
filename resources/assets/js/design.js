import { ready } from './ready';

ready(() => {
  [...document.getElementsByClassName('prevent-default')].forEach((element) => {
    let preventDefault = (e) => {
      e.preventDefault();
      return false;
    };

    element.addEventListener('submit', preventDefault);
    element.addEventListener('click', preventDefault);
  });
});
