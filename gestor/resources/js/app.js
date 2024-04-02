import './bootstrap';


$(document).ready(function(){
  var checkboxes = $('.permcheckboxes');
  checkboxes.change(function(){
      if($('.permcheckboxes:checked').length>0) {
          checkboxes.removeAttr('required');
      } else {
          checkboxes.attr('required', 'required');
      }
  });
});