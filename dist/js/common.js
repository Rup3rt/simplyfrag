'use strict'

$(function() {
    $("#bs-example-navbar-collapse-1  li ").click(function() {
        $("#bs-example-navbar-collapse-1 li ").removeClass("active");
        $(this).toggleClass("active");
    })
});



$(".popup").click(function(){
    $("#myPopup").css("visibility","visible");

    $(".popup_wrapper").css("visibility","visible");
    $("#myPopup").css("opacity","1");

    return false

});
$("#img_close").click(function(){
    $("#myPopup").css("visibility","hidden");
    $(".popup_wrapper").css("visibility","hidden");
    return false

});

function initSlider()
    {
        $('.my_carousel').slick({
            slidesToShow: 4,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        arrows: true,
                        centerMode: true,

                        slidesToShow: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        arrows: true,
                        centerMode: true,
                        centerPadding: '40px',
                        slidesToShow: 1
                    }
                }
            ]
        });
    }

$(document).on('ready', function () {
    initSlider();
});