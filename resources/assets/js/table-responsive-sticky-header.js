import { ready } from './ready';

ready(() => {
  const navbar = document.querySelector('.navbar');

  document.querySelectorAll('.table-responsive-sticky-header').forEach((element) => {
    const table = element.querySelector('table');
    const header = table.querySelector('thead');

    table.className += ' table-sticky-header';

    const update = () => {
      const calcHeight = navbar.getBoundingClientRect().height - element.getBoundingClientRect().top;
      header.style.top = `${calcHeight}px`;
    };

    update();
    window.addEventListener('resize', update);
    window.addEventListener('scroll', update);

    const navbarObserver = new MutationObserver(update);
    navbarObserver.observe(navbar, { attributes: true, subtree: true, attributeFilter: ['class'] });
  });
});
