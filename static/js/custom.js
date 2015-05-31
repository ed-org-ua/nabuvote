
$(document).ready(function(){
  /*!
   * IE10 viewport hack for Surface/desktop Windows 8 bug
   */
  (function () {
    'use strict';
    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
      var msViewportStyle = document.createElement('style')
      msViewportStyle.appendChild(
        document.createTextNode(
          '@-ms-viewport{width:auto!important}'
        )
      )
      document.querySelector('head').appendChild(msViewportStyle)
    }
  })();

  $('.show-candidates').click(function(event){
    event.preventDefault();
    $('.candidates_list').show(500);
    return false;
  });

  (function(){
    var max_selected_limit = window.max_selected_limit;
    $('.candidates_table input[type=checkbox]').click(function(event){
      var selected_count = $('.candidates_table input:checked').length;
      if (selected_count > max_selected_limit) {
        event.preventDefault();
        event.stopPropagation();
        setTimeout(function(){
          alert("Ви обрали більше ніж дозволено кандидатів.");
        }, 500);
        return false;
      }
    });
  })();

  (function(){
    var current_session_lifetime = window.current_session_lifetime;
    if (window.vote_timer)
      clearInterval(window.vote_timer);
    window.vote_timer = setInterval(function(){
      if (current_session_lifetime < 15) {
        $('.timer_text').html('Час сплив. Будь ласка, переголосуйте.');
        clearInterval(window.vote_timer);
        return;
      }
      window.current_session_lifetime = current_session_lifetime - 1;
      var ts = Math.floor(current_session_lifetime/60) + ' хв.';
      $('.countdown').html(ts);
    }, 1000);
  })();

});
