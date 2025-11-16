const slider = document.querySelector('.slider');
const next = document.querySelector('.next');
const prev = document.querySelector('.previous');

let scrollAmount = 0;

next.addEventListener('click', () => {
  slider.scrollBy({ left: 300, behavior: 'smooth' });
});

prev.addEventListener('click', () => {
  slider.scrollBy({ left: -300, behavior: 'smooth' });
});
