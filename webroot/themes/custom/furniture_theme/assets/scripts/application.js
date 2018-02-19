(function($) {

	'use strict';

    // Function for tabs on furniture node page.
    Drupal.behaviors.tabLinks = {
        attach:function(context, settings) {

            $('body .node--type-furniture .second-block ul.tabs li').click(function(){
                var tab_id = $(this).attr('data-tab');

                $('body .node--type-furniture .second-block ul.tabs li').removeClass('current');
                $('body .node--type-furniture .second-block .tab-content').removeClass('current');

                $(this).addClass('current');
                $("#"+tab_id).addClass('current');
            });

            $('body #block-latestfromblogblock ul.tabs li').click(function(){
                var tab_id = $(this).attr('data-tab');

                $('body #block-latestfromblogblock ul.tabs li').removeClass('current');
                $('body #block-latestfromblogblock .tab-content').removeClass('current');

                $(this).addClass('current');
                $("#"+tab_id).addClass('current');
            });

        }
    };

    // Function for adding new class on li>ul if there is another ul inside

    Drupal.behaviors.newClassForMenu = {
        attach:function(context) {
            $('#block-furniture-theme-main-menu li.nav-list-1', context).has('ul').parent('ul').addClass('has-children');

        }

    };

    Drupal.behaviors.newClassMobMenu = {
        attach:function(context) {
            $('li.menu-item-2', context).has('ul').addClass('got-pseudo');
            $('li.menu-item-1', context).has('ul').addClass('got-pseudo');

        }

    };

    Drupal.behaviors.mobileMenue = {
        attach: function (context) {

            // Function for mobile version for main navigation

            $('.menu--main-navigation-mobile > .menu-toggle', context).on('click', function () {
                // console.log('test');
                $('.menu--main-navigation-mobile').toggleClass('first-lev-show');
                $('.menu-item-1').removeClass('show');
            });

            $('.menu-level-1 > .menu-item-1>a', context).on('click', function (e) {
                if ($(this).siblings('ul').length > 0) {
                    e.preventDefault();
                    $(this).parent().toggleClass('show');
                }

            });

            $('li.menu-item-2>a', context).on('click', function (e) {
                console.log($(this).siblings('ul').length);
                if ($(this).siblings('ul').length > 0) {
                    e.preventDefault();
                    // $(this).addClass('got-pseudo');
                    $(this).parent().toggleClass('show');
                }

            });

        }
    };

    // Function for mobile version for account navigation

    Drupal.behaviors.accountMenu = {
        attach: function (context) {


            $('.menu--account > .account-menu-toggle', context).on('click', function () {

                $('.menu--account').toggleClass('menu-show');
            });

        }
    };

    // Add span

    Drupal.behaviors.addSpan = {
        attach: function (context) {

            function ide() {
                $('.region-header', context).append('<span class="close-header"></span>');
            }

            if ($(window).width() < 1055)  {
                ide();
            }
            $(window).resize(function() {
                if ($('.region-header').has('span.close-header')) {
                    return;
                }

                ide();
            });
        }
    };

    // Region header collapse

    Drupal.behaviors.regionHeader = {
        attach: function (context) {

            $('.close-header', context).on('click', function () {
                $('.region-header', context).toggleClass('header-show');
            });

        }
    };


    // Function for 'back to top' button

    Drupal.behaviors.backToTop = {
        attach:function(context) {
            var btn = $('#block-backtotop a');

            $(window).scroll(function() {
                if ($(window).scrollTop() > 100) {
                    btn.addClass('show');
                } else {
                    btn.removeClass('show');
                }
            });

            btn.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({scrollTop:0}, '100');
            });
        }
    };

    Drupal.behaviors.sliderBlog = {
      attach:function() {
              $('.from-the-blog .flexslider').flexslider({
                  animation: "slide",
                  animationLoop: true,
                  itemWidth: 420,
                  itemMargin: 10,
                  minItems: 1,
                  maxItems: 2
              });

          $('.about_us .flexslider').flexslider({
              animation: "slide",
              animationLoop: true,
              itemWidth: 200,
              itemMargin: 10,
              minItems: 1,
              maxItems: 3
          });


          $('.first-left-block #carousel').flexslider({
              animation: "slide",
              controlNav: false,
              animationLoop: false,
              slideshow: false,
              itemWidth: 60,
              itemMargin: 4,
              asNavFor: '#slider'
          });

          $('.first-left-block #slider').flexslider({
              animation: "slide",
              controlNav: false,
              animationLoop: false,
              slideshow: false,
              itemWidth: 420,
              sync: "#carousel"
          });

          $('.block-hero-slider .flexslider').flexslider({
              animation: "slide",
              controlsContainer: $(".custom-controls-container"),
              customDirectionNav: $(".custom-navigation a"),
              slide: false
          });

      }
    };


    // Magnifier zoom for furniture single node page
    Drupal.behaviors.magnifierZoom = {
        attach:function(context) {
            // Create an image object from the full size image - used to get dimensions
            var selector = ".image-style-furniture-default-img";
            var $mag = $(".magnifier");
            var image_object = new Image();
                image_object.src = $(selector).attr("src");
            var originalImagePath = image_object.src.replace('styles/furniture_default_img/public/', '');

            function magnifier() {
                var full_size_width  = 0;
                var full_size_height = 0;
                $mag.css("background","url('" + originalImagePath  + "') no-repeat");

                // Set up the event handler for when the mouse moves within #container
                $(".first-block li#container", context).on('mousemove', function(e){
                    e.preventDefault();
                    // Get the full size image dimensions the first time though this function
                    if(!full_size_width && !full_size_height)  {
                        // Get the dimension of the full size image
                        full_size_width  = image_object.width;
                        full_size_height = image_object.height;
                    } else {
                        // Get the x,y coordinates of the mouse with respect to #container
                        var container_offset = $(this).offset();
                        var mx = e.pageX - container_offset.left;
                        var my = e.pageY - container_offset.top;
                        // Fade out the magnifying glass when the mouse is outside the container
                        if(mx < $(this).width() && my < $(this).height() && mx > 0 && my > 0)  {
                            $mag.fadeIn(100);
                        } else {
                            $mag.fadeOut(100);
                        }
                        if($mag.is(":visible")) {
                            // Calculate the magnifier position from the mouse position
                            var px = mx - $mag.width(400)*4;
                            var py = my - $mag.height(400)*4;
                            // Calculate the portion of the background image that is visible in the magnifier
                            // using the ratio in size between the full size and small images
                            var rx = -1 * Math.round(mx / $(selector).width()  * full_size_width  - $mag.width() / 4);
                            var ry = -1 * Math.round(my / $(selector).height() * full_size_height - $mag.height() / 4);
                            var bgp = rx + "px " + ry + "px";
                            // Update the position of the magnifier and the portion of the background image using CSS
                            $mag.css({left: px, top: py, backgroundPosition: bgp});
                        }
                    }
                });

            }

            magnifier();

            $(selector, context).on('click', function() {
                originalImagePath = $(this).attr("src").replace('styles/furniture_default_img/public/', '');
                magnifier();
            });

            $(".node--type-furniture #slider " + selector, context).on('mouseleave', function() {
                $mag.fadeOut(100);
            });
        }
    };


    // Masonry for portfolio list page

    Drupal.behaviors.imgPortfolio = {
        attach:function() {
            $('#block-portfoliolistpageblock .block-portfolio-img img').once("img-loaded").each(function () {

                $(this).show(1000, function () { // Show the image when loaded

                    $(this).masonry({ // After that, trigger the .masonry()

                        columnWidth: 'div.block-portfolio-img',
                        itemSelector: "div.post",
                        percentPosition: true,
                        isAnimated: true

                    });

                });
            });
        }
    };


    // Main navigation fix [desktop]

    Drupal.behaviors.mainNavFixed = {
        attach:function(context) {
            var wrap = $(".layout-container");

            if ($(window).width() > 1000) {
                $(window).scroll(function () {
                    if ($(window).scrollTop() > 35) {
                        wrap.addClass("fix-navbar");
                    } else {
                        wrap.removeClass("fix-navbar");
                    }
                });
            }
        }
    };


    // Range price filter

    Drupal.behaviors.priceFilter = {
        attach: function (context) {

            $( "#slider-range" ).slider({
                range: true,
                min: 0,
                max: 500,
                values: [ 75, 300 ],
                slide: function( event, ui ) {
                    $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
                    $('.form-item-min-price > input[data-drupal-selector="edit-min-price"]').val("" + ui.values[ 0 ] + "");
                    $('.form-item-max-price > input[data-drupal-selector="edit-max-price"]').val("" + ui.values[ 1 ] + "");
                }
            });
            $( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
                " - $" + $( "#slider-range" ).slider( "values", 1 ) );



        }
    };


})(jQuery);





//# sourceMappingURL=../scripts/application.js.map
