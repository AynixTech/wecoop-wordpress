jQuery(document).ready(function($) {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.iniziative-carousel', {
            loop: true,
            spaceBetween: 30,
            slidesPerView: 3,

            pagination: {
                el: '.iniziative-carousel .swiper-pagination',
                clickable: true,
            },

            navigation: {
                nextEl: '.iniziative-carousel .swiper-button-next',
                prevEl: '.iniziative-carousel .swiper-button-prev',
            },

            breakpoints: {
                0: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });
    } else {
        console.error('Swiper non trovato!');
    }
const partnerSwiper = new Swiper('.swiper-partners', {
    slidesPerView: 'auto',
    spaceBetween: 20,
    loop: true,
    navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev'
  },
    breakpoints: {
      0: {
        slidesPerView: 2,
      },
      600: {
        slidesPerView: 2,
      },
      900: {
        slidesPerView: 3,
      },
      1200: {
        slidesPerView: 4,
      }
    }
  });
});
