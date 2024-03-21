@extends('layouts.app')
@section('title', 'Gestor SPRI - Boletín EEN Basquetrade')
@section('maintitle')
    <h1>{!! trans('messages.boletin_basquetrade') !!}</h2>
@stop
@section('content')
    <div class="row">
        <div class="col-6">
            <div id="form" class="col-12"></div>
        </div>
        <div class="col-6">
            <iframe src="" width="100%" height="100%"></iframe>
        </div>
    </div>
@stop
@section('footer')
<div class="lateral">
    <button id="newtitle" class="btn btn-info btn-sm">Título</button>
    <button id="newitem" class="btn btn-success btn-sm">Noticia</button>
    <button id="newseparator" class="btn btn-warning btn-sm">Separador</button>
    <button id="newspaciator" class="btn btn-warning btn-sm">Espaciador</button>
    <hr/>
    <button id="generate" class="btn btn-primary btn-sm">Generar boletín</button>
    <input id="namesave" type="text" class="form-control" value="" placeholder="Nombre para guardar" />
    <button id="save" class="btn btn-secondary btn-sm">Guardar</button>
    <hr/>
    <select class="form-select" id="loadfile"></select>
    <button id="load" class="btn btn-secondary btn-sm">Cargar</button>
    <hr/>
    <input id="sendemail" type="email" placeholder="Email separados por comas" class="form-control" value="">
    <button id="send" class="btn btn-secondary btn-sm">Enviar</button>
</div>
<style>
    #form {
        overflow: auto;
        height: calc(100vh - 150px);
    }
    #form div {
        position: relative;
        padding-right: 40px;
        border: 1px solid transparent;
    }
    .lateral {
        position: fixed;
        top: 15%;
        left: 0px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 10px;
        background-color: #cecece;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
        max-width: 200px;
    }
    #form div i {
        display: block;
        position: absolute;
        top: 6px;
        right: 10px;
        font-size: 20px;
        cursor: pointer;
    }
    .container .row {
        min-height: calc(100vh - 150px);
    }
    #advise {
        background-color: #cdcdcd;
        position: absolute;
        top: 10%;
        width: 200px;
        min-height: 20px;
        left: calc(50% - 50px);
        padding: 20px;
        display: none;
        border: 1px solid #000;
        text-align: center;
        text-align: center;
         word-break: break-word;
    }

    #form > div {
        border: 2px solid white;
    }

    #form > div.focus {
        border: 2px solid red;
    }
</style>
<script>
    var item = 1;
    var iframe = '{{ env('APP_URL') }}/temp/basquetrade-temp.html';
    var focusDiv;
    regeneratePreview();
    function loadSaves() {
        var date = new Date().toJSON();
        $("#loadfile option").remove(); 
        $.getJSON( "{{ route('boletines.been-basquetrade.saves') }}?hash="+date, function( data ) {
            $.each( data, function( key, val ) {
                $("#loadfile").append('<option value="'+val+'">'+val.replace(".json", "")+'</option>');
            });
        });
    }

    function regeneratePreview(action = 'generate') {
        var form = [];
        $("#form div").each(function() {
            var values = []
            $(this).children("input, textarea, select").each(function() {
                values.push($(this).val());
            });
            form.push({
                'type': $(this).data("type"),
                'value': values
            });
        });

        $.post({
            url: "{{ route('boletines.been-basquetrade.ajax.post') }}",
            data: {
                action: action,
                form: form,
                email: (action == 'send' ? $("#sendemail").val() : ""),
                namesave: (action == 'save' ? $("#namesave").val() : ""),
            }
        }).done(function(data) {
            if(action == 'save') loadSaves();
            var date = new Date().toJSON();
            $('iframe').attr( 'src', function () { return iframe+"?hash="+date; });
            if(data.status) showAdvise(data);
        });
    }

    function showAdvise(json) {
        $("#advise").html("");
        $("#advise").addClass(" bg-"+json.status);
        $("#advise").html(json.text);
        $("#advise").fadeIn().delay(2000).fadeOut();
    }

    $(function() {
        $( "#form" ).sortable({
            update: function() {
                regeneratePreview();
            }
        });
        loadSaves();
    });

    $(document).on('click', "#form > div", function() {
        $("div").not(this).removeClass("focus");
        $(this).addClass("focus");
        focusDiv = this;
    });

    $("#newitem").click(function(e) {
        $("div").not(this).removeClass("focus");
        var append = "<div id='item-"+item+"' data-type='item' class='focus col-12 bg-success p-2 mb-2 pe-5' tabindex='0'>"+
            "<input type='text' placeholder='Título' class='form-control form-control-sm mb-1' value=''>"+
            "<input type='text' placeholder='Subtítulo' class='form-control form-control-sm mb-1' value=''>"+
            "<input type='url' placeholder='URL' class='form-control form-control-sm mb-1' value=''>"+
            "<textarea placeholder='Descripción' class='form-control'></textarea>"+
            "<select class='form-select form-select-sm'><option value='efefed'>Gris</option><option value='ffffff'>Blanco</option></select>"+
            "<i>&#x2715;</i></div>";
        if(jQuery(focusDiv).length) $(focusDiv).after(append);
        else $("#form").append(append);
        focusDiv = $("#item-"+item);
        item++;
        regeneratePreview();
    });
    
    $("#newseparator").click(function(e) {
        $("div").not(this).removeClass("focus");
        var append = "<div id='item-"+item+"' data-type='separator' class='focus col-12 bg-warning p-2 mb-2 pe-5' tabindex='-1'><hr><i>&#x2715;</i></div>";
        if(jQuery(focusDiv).length) $(focusDiv).after(append);
        else $("#form").append(append);
        focusDiv = $("#item-"+item);
        item++;
        regeneratePreview();
    });

    $("#newspaciator").click(function(e) {
        $("div").not(this).removeClass("focus");
        var append = "<div id='item-"+item+"' data-type='spaciator' class='focus col-12 bg-warning p-2 mb-2 pe-5' tabindex='-1'>"+
            "<select class='form-select form-select-sm'>"+
            "<option value='efefed'>Gris</option>"+
            "<option value='ffffff'>Blanco</option>"+
            "</select>"+
            "<select class='form-select form-select-sm'>"+
            "<option value='20'>Pequeño</option>"+
            "<option value='40'>Mediano</option>"+
            "<option value='60'>Grande</option>"+
            "</select> <i>&#x2715;</i></div>";
        if(jQuery(focusDiv).length) $(focusDiv).after(append);
        else $("#form").append(append);
        focusDiv = $("#item-"+item);
        item++;
        regeneratePreview();
    });

    $("#newbutton").click(function(e) {
        $("div").not(this).removeClass("focus");
        var append = "<div id='item-"+item+"' data-type='button' class='focus col-12 bg-secondary p-2 mb-2 pe-5' tabindex='-1'>"+
            "<select class='form-select form-select-sm'>"+
            "<option value='gris'>Gris</option>"+
            "<option value='azul'>Azul</option>"+
            "</select>"+
            "<input class='form-control form-control-sm' type='text' value='' placeholder='URL'> <i>&#x2715;</i></div>";
        if(jQuery(focusDiv).length) $(focusDiv).after(append);
        else $("#form").append(append);
        focusDiv = $("#item-"+item);
        item++;
        regeneratePreview();
    });
    
    $("#newtitle").click(function(e) {
        $("div").not(this).removeClass("focus");
        var append = "<div id='item-"+item+"' data-type='title' class='focus col-12 bg-info p-2 mb-2 pe-5'>"+
            "<b>TITULAR</b> "+
            "<select class='form-select form-select-sm'>"+
            "<option value='1'>PROYECTOS</option>"+
            "<option value='2'>EVENTOS</option>"+
            "</select>"+
            "<i>&#x2715;</i>"+
            "</div>";
        if(jQuery(focusDiv).length) $(focusDiv).after(append);
        else $("#form").append(append);
        focusDiv = $("#item-"+item);
        item++;
        regeneratePreview();
    });

    $(document).on('click', "i", function() {
        jQuery(this).parent().remove();
        regeneratePreview();
    });

    $(document).on('change', "#form input, #form select", function() {
        regeneratePreview();
    });
    
    $("#generate, #save").click(function(e) {
        regeneratePreview($(this).attr("id"));
    });

    $("#send").click(function(e) {
        if($("#sendemail").val() != '') {
            regeneratePreview('send');
        }
    });

    $("#load").click(function(e) {
        item = 1;
        $("#form").html('');
        $.getJSON( "{{ route('boletines.been-basquetrade.saves') }}?load="+$("#loadfile").val(), function( data ) {
            $.each( data, function( key, val ) {
                $("#new"+val.type).click();
                if(val.value) {
                    var $currentitem = item - 1;
                    $("#item-"+$currentitem+" input, #item-"+$currentitem+" select, #item-"+$currentitem+" textarea").each(function(index) {
                        $(this).val(val.value[index]);
                    });
                }
            });
            regeneratePreview();
        });
    });
</script>
<div id="advise"></div>
@stop