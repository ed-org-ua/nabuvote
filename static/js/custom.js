/* Copyright © 2015-2019 e-Democracy NGO Ukraine info@ed.org.ua */
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
    function update_selected_list(event) {
      var sel = [];
      $('.candidates_table tr').removeClass('info').each(function (index){
        var $this = $(this),
          checked = $this.find('input:checked').length;
        if (checked) {
          $this.addClass('info');
          sel.push($this.children('td').first().text());
        }
        if (sel.length)
          $('#selected_candidates').html(sel.join(" <br>\n"));
        else
          $('#selected_candidates').html('<i>Не обрано жодного</i>');
      });
    }
    function update_candidates_left(event) {
      var selected_count = $('.candidates_table input:checked').length;
      var remains_choose = max_selected_limit - selected_count;
      if (selected_count > max_selected_limit) {
        event.preventDefault();
        event.stopPropagation();
        setTimeout(function(){
          alert("Ви вже обрали максимальну кількість кандидатів.");
        }, 100);
        return false;
      }
      if (remains_choose == 1)
        text = '1 кандидата';
      else
        text = ' '+remains_choose+' кандидатів';
      $('.candidates_left').html(text);
      if (selected_count == 1)
        text = '1 кандидата';
      else
        text = ' '+selected_count+' кандидатів';
      $('.candidates_selected').html(text);
      return update_selected_list(event);
    }
    if (max_selected_limit)
      update_candidates_left();
    $('.candidates_table input[type=checkbox]').click(function(event){
      return update_candidates_left(event);
    });
  })();

  (function(){
    var current_session_lifetime = window.current_session_lifetime;
    function update_timer_text() {
      if (current_session_lifetime < 5) {
        clearInterval(window.vote_timer);
        setTimeout(function() {
          alert('Час сплив. Будь ласка, переголосуйте.');
          window.location = 'step1.php';
        }, 100);
        return false;
      }
      current_session_lifetime = current_session_lifetime - 1;
      var ts = Math.floor(current_session_lifetime/60) + ' хв.';
      $('.countdown').html(ts);
    }
    if (window.vote_timer)
      clearInterval(window.vote_timer);
    if ($('.timer_text').length && current_session_lifetime) {
      window.vote_timer = setInterval(update_timer_text, 1000);
      update_timer_text();
    }
  })();

});
