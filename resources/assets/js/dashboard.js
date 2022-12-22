import { ready } from './ready';

ready(() => {
  if (!document.getElementById('public-dashboard')) return;

  // reload page every minute
  setInterval(async () => {
    const response = await fetch(window.location.href);

    if (!response.ok) {
      console.warn('error loading dashboard');
      return;
    }

    const responseData = await response.text();
    const parser = new DOMParser();
    const dummyDocument = parser.parseFromString(responseData, 'text/html');
    const dashboardContent = dummyDocument.getElementById('public-dashboard');
    document.querySelector('#content .wrapper').innerHTML = dashboardContent.outerHTML;
  }, 60000);

  // Handle fullscreen button
  // - Remove some elements from UI
  // - Add "Public Dashboard" to title
  document.getElementById('dashboard-fullscreen')?.addEventListener('click', (event) => {
    event.preventDefault();
    const removeElementsSelector = '#navbar-collapse-1,.navbar-nav,.navbar-toggler,#footer,#fullscreen-button';
    document.querySelectorAll(removeElementsSelector).forEach((element) => {
      element.parentNode.removeChild(element);
    });

    document.querySelector('.navbar-brand')?.appendChild(document.createTextNode('Dashboard'));
  });
});
