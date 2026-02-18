import { ready } from './ready';

ready(() => {
  [...document.getElementsByClassName('prevent-default')].forEach((element) => {
    const preventDefault = (e) => {
      e.preventDefault();
      return false;
    };

    element.addEventListener('submit', preventDefault);
    element.addEventListener('click', preventDefault);
  });

  document.getElementById('delete-form')?.addEventListener('submit', (event) => {
    event.preventDefault();
    console.log('Delete confirmed');
  });
});
