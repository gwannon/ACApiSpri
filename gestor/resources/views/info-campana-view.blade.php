@extends('layouts.simple')
@section('content')
  {!! $message['text'] !!}
@stop
@section('footer')
<style>
  #control {
    position: fixed;
    top: 0px;
    right: 0px;
    text-align: center;
    font-weight: 700;
    width: 130px;
    background-color: #fff;
  }
  #blue,
  #green,
  #orange,
  #red,
  #purple,
  #black {
    color: white;
    padding: 10px;
    background-color: black;
  }
  #blue { background-color: #2196f3; }
  #green { background-color: green; }
  #orange { background-color: orange; }
  #red { background-color: red; }
  #purple { background-color: purple; }

  .heatmap {
    text-shadow: 0 0 3px #000;
    position: absolute;
    color: white;
    font-size: 20px;
    z-index: 10;
    min-width: 30px;
    min-height: 20px;
    font-weight: 700;
  }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>var view_campaign_id = {{ $message['campaign_id'] }};</script>
<script>
  jQuery(document).ready(function () {
    jQuery('a').css('display', 'inline-block');
    jQuery('#control > div > input[type=radio]').change(function() {
    	if (jQuery('input[name=clicks]').is(':checked')) {
		    jQuery('.heatmap').remove();
		    jQuery('#blue,#green,#orange,#red,#stats,#purple,#black').html('--');
		    loadHeatMap(view_campaign_id);
		  }
    });
  });
  
  function loadHeatMap(id) {
    jQuery.ajax({
      url : '{{ route('info-view.ajax') }}',
      data : {
        campaign_id: id,
        test: jQuery('input[name=test]:checked').val()
      },
      type : 'GET',
      dataType : 'json',
      beforeSend: function () { jQuery('a img').css('opacity', '1'); },
      success : function(json) {
        var uniquelinkclicks = json.uniquelinkclicks;
        var linkclicks = json.linkclicks;
        var countlinks = json.links.length;

        if(jQuery('input[name=clicks]:checked').val() == 'clicks')  {
            var step = Math.ceil(linkclicks / countlinks);
        } else {
            var step = Math.ceil(uniquelinkclicks / countlinks);
        }
        if(step < 1) step = 1;
        jQuery('#blue,#green,#orange,#red,#stats,#purple,#black').html('--');
        jQuery('#stats').html(uniquelinkclicks+'/'+linkclicks);
        jQuery('#blue').html('>= '+(step * 3));
        jQuery('#green').html((step * 2)+' - '+((step * 3)-1));
        jQuery('#orange').html(step+' - '+((step * 2)-1));
        jQuery('#red').html((Math.ceil(step/2)+1)+' - '+(step - 1));
        jQuery('#purple').html('1 - '+Math.ceil(step/2));
        jQuery('#black').html('0');
        var color = '#00000057';
        if(jQuery('input[name=clicks]:checked').val() == 'clicks')  {
            json.links.forEach(function(data, index) {
                if(data.linkclicks >= (step * 3)) color = '#2196f396';
                else if(data.linkclicks >= (step * 2)) color = '#00800085';
                else if(data.linkclicks >= step) color = '#ffa50096';
                else if(data.linkclicks >= (Math.ceil(step/2)+1)) color = '#ff000057';
                else if(data.linkclicks > 0) color = '#240365bd';
                else color = '#00000057';
                generateZone(data, color);
            });
        } else {
            json.links.forEach(function(data, index) {
                if(data.uniquelinkclicks >= (step * 3)) color = '#2196f396';
                else if(data.uniquelinkclicks >= (step * 2)) color = '#00800085';
                else if(data.uniquelinkclicks >= step) color = '#ffa50096';
                else if(data.uniquelinkclicks >= (Math.ceil(step/2)+1)) color = '#ff000057';
                else if(data.uniquelinkclicks > 0) color = '#240365bd';
                else color = '#00000057';
                generateZone(data, color);
            });
        }
      },
      error : function(xhr, status) { },
      complete : function(xhr, status) { jQuery('a img').css('opacity', '0.45'); }
    });
  }

  function generateZone(data, color) {
    jQuery('a[href$=\"'+data.link+'\"]').each(function(index) {
      position = jQuery(this).position();
      console.log(position);
      if(jQuery('input[name=clicks]:checked').val() == 'clicks')  { 
        jQuery('body').append('<div id=\"hm'+index+'\" data-link=\"'+data.link+'\" class=\"heatmap\" style=\"top:'+position.top+'px; left: '+position.left+'px; width: '+jQuery(this).outerWidth()+'px; height: '+jQuery(this).outerHeight()+'px; background-color: '+color+';\">'+(data.linkclicks != '' ? data.linkclicks : '0')+' ('+data.linkclickspercent+' %)</div>');
      } else {
        jQuery('body').append('<div id=\"hm'+index+'\" class=\"heatmap\" style=\"top:'+position.top+'px; left: '+position.left+'px; width: '+jQuery(this).outerWidth()+'px; height: '+jQuery(this).outerHeight()+'px; background-color: '+color+';\">'+(data.uniquelinkclicks != '' ? data.uniquelinkclicks : '0')+' ('+data.uniquelinkclickspercent+'%)</div>');
      }
    });
  }
</script>
  <div id='control'>
    <div>Mapa de Calor</div>
    <div>
      <input type='radio' name='clicks' value='uniqueclicks' title='Clicks únicos'>
      <input type='radio' name='clicks' value='clicks' title='Clicks totales'><br/>
    </div>
    <div><small>Únicos/Totales</small></div>
    <div id='stats' style='padding: 10px; '>--</div>
    <div id='blue' title='Por encima de la media x3'>--</div>
    <div id='green' title='Por encima de la media x2'>--</div>
    <div id='orange' title='Por encima de la media'>--</div>
    <div id='red' title='Por debajo de la media'>--</div>
    <div id='purple' title='Por debajo de la mitad de la media'>--</div>
    <div id='black' title='0 clicks'>--</div>
  </div>
</body>
</html>
@stop