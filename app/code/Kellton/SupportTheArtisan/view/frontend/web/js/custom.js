require([
            'jquery',
        ],
        function(
            $
            
        ){

// JavaScript Document
$(document).ready(function() {
    // sticky header
$(document).on('scroll',function(){
    if($(this).scrollTop() > 100){
        
        $('.navnemu').addClass('bg-white');
                
    }else{
        $('.navnemu').removeClass('bg-white');			
    };
});
//      //Click event to scroll to top

//       if ($(window).width() < 577) {
//           $('.scroll1').click(function() {
//             $('html, body').animate({
//               scrollTop: 650
//             }, 900);
//             return false;   
//           }); 
//           $('.onscrollstop').click(function() {
//             $('html, body').animate({
//               scrollTop: 650
//             }, 900);
//             return false;   
//           }); 
//       }
//       if ($(window).width() > 577 && $(window).width() < 991) {
//         $('.scroll1').click(function() {
//           $('html, body').animate({
//             scrollTop: 588
//           }, 900);
//           return false;   
//         }); 
//         $('.onscrollstop').click(function() {
//           $('html, body').animate({
//             scrollTop: 588
//           }, 900);
//           return false;   
//         });
//     }
//     if ($(window).width() > 992 && $(window).width() < 1200) {
//       $('.scroll1').click(function() {
//         $('html, body').animate({
//           scrollTop: 680
//         }, 900);
//         return false;   
//       }); 
//       $('.onscrollstop').click(function() {
//         $('html, body').animate({
//           scrollTop: 680
//         }, 900);
//         return false;   
//       });
//   }
//   if ($(window).width() > 1200) {
//     $('.scroll1').click(function() {
//       $('html, body').animate({
//         scrollTop: 800
//       }, 900);
//       return false;   
//     }); 
//     $('.onscrollstop').click(function() {
//       $('html, body').animate({
//         scrollTop: 800
//       }, 900);
//       return false;   
//     });
// }
// $('.scroll2').click(function() {
//   $('html, body').animate({
//     scrollTop: 0
//   }, 900);
//   return false;   
// }); 
     // declare variable
     var scrollTop = $(".scrollTop");

     $(window).scroll(function() {
       // declare variable
       var topPos = $(this).scrollTop();
   
       // if user scrolls down - show scroll to top button
       if (topPos > 100) {
         $(scrollTop).css("opacity", "1");
   
       } else {
         $(scrollTop).css("opacity", "0");
       }
   
     }); // scroll END
   

       //-------------smooth scroll----------------------
//   var GetHaderHightId = $(".craft-card").outerHeight();
//   $('a[href*=\\#]:not([href$=\\#])').click(function() {
//     event.preventDefault();	
//     $('html, body').animate({
// 		scrollTop: $($.attr(this, 'href')).offset().top - GetHaderHightId
//     }, 1000);
// });
$(document).ready(function(){
  $(".craft-card a[href*=\\#]:not([href$=\\#])").on('click', function(event) {
    if (this.hash !== "") {
      event.preventDefault();
      var hash = this.hash;
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){
        window.location.hash = hash;
      });
    } 
  });
});
$(document).ready(function(){
  $("a[href*=\\#]:not([href$=\\#])").on('click', function(event) {
    if (this.hash !== "") {
      event.preventDefault();
      var hash = this.hash;
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){
        window.location.hash = hash;
      });
    } 
  });
});
$(document).ready(function(){
  $("a[href*=\\#]:not([href$=\\#])").on('click', function(event) {
    if (this.hash !== "") {
      event.preventDefault();
      var hash = this.hash;
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){
        window.location.hash = hash;
      });
    } 
  });
});
$('.scroll2').click(function() {
  $('html, body').animate({
    scrollTop: 0
  }, 900);
  return false;   
}); 

});
});
