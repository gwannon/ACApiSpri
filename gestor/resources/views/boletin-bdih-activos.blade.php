@extends('layouts.app')
@section('title', 'Gestor SPRI - Boletín BDIH Activos tecnólogicos')
@section('maintitle')
    <h1>{!! trans('messages.boletin_bdih_activos') !!}</h2>
@stop
@section('content')
    <div class="row">
        <div class="col-4 offset-2">
            <div id="form" class="col-12"></div>
        </div>
        <div class="col-6">
            <iframe src="" width="100%" height="100%"></iframe>
        </div>
    </div>
@stop
@section('footer')
<div class="lateral">
    <select class="form-select" id="lang">
        <option value="es">ES</option>
        <option value="eu">EU</option>
    </select>
    <button id="newtitle" class="btn btn-info btn-sm">Título</button>
    <select class="form-select" id="newitemcontent">
    </select>
    <button id="newitem" class="btn btn-success btn-sm">Noticia</button>
    <button id="newspaciator" class="btn btn-warning btn-sm">Espaciador</button>
    <button id="newbutton" class="btn btn-secondary btn-sm">Botón</button>
    <button id="newbanner" class="btn btn-primary btn-sm">Banner</button>
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
    <hr/>
    <a href="/files/bdih-activos/esquema.pdf" target="_blank" class="btn btn-secondary btn-sm">Ayuda</a>
</div>
<style>
    #form {
        overflow: auto;
    height: 100vh;
    }
    #form div {
        position: relative;
        padding-right: 40px;
        border: 1px solid transparent;
    }
    .lateral {
        position: fixed;
        top: 3%;
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
        min-height: 100vh;
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
    var items = [];
    loadItems();
    function loadItems() {
        $("#newitemcontent option").remove(); 
        $("#newitemcontent").append('<option val="-1">-- Noticia en blanco --</option>');
        $.getJSON( "{{ route('boletines.bdih-activos.items') }}?lang="+$('select#lang').val(), function( data ) {
            $.each( data, function( key, val ) {
                items[key] = val;
                $("#newitemcontent").append('<option value="'+key+'">'+val.type+" - "+val.date+" - "+val.title+'</option>');
            });
        });
    }
    var item = 1;
    var iframe = '{{ env('APP_URL') }}/temp/bdih-activos.html';
    var focusDiv;
    regeneratePreview();
    function loadSaves() {
        var date = new Date().toJSON();
        $("#loadfile option").remove(); 
        $.getJSON( "{{ route('boletines.bdih-activos.saves') }}?hash="+date+"&lang="+$('select#lang').val(), function( data ) {
            $.each( data, function( key, val ) {
                $("#loadfile").append('<option value="'+val+'">'+val.replace("-", " (").replace(".json", "").replace("_", " ")+'</option>');
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
            url: "{{ route('boletines.bdih-activos.ajax.post') }}",
            data: {
                action: action,
                form: form,
                lang: $('select#lang').val(),
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
        itemid = $("#newitemcontent").val();
        console.log(itemid);
        var append = "<div id='item-"+item+"' data-type='item' class='focus col-12 bg-success p-2 mb-2 pe-5' tabindex='0'>"+
            "<input type='text' placeholder='Título' class='form-control form-control-sm mb-1' value='"+(itemid > 0 ? items[itemid].title : "")+"'>"+
            "<input type='text' placeholder='Subtítulo' class='form-control form-control-sm mb-1' value=''>"+
            "<input type='url' placeholder='Imagen URL' class='form-control form-control-sm mb-1' value='"+(itemid > 0 ? items[itemid].image : "")+"'>"+
            "<input type='url' placeholder='URL' class='form-control form-control-sm mb-1' value='"+(itemid > 0 ? items[itemid].url : "")+"'>"+
            "<textarea placeholder='Descripción' class='form-control'>"+(itemid > 0 ? items[itemid].description : "")+"</textarea>"+
            "<select class='form-select form-select-sm'>"+
            "<option value='basic'>Normal</option>"+
            "<option value='event'>Evento</option>"+
            "<option value='case'>Caso de éxito</option>"+
            "<option value='featured'>Destacado</option>"+
            "</select>"+
            "<i>&#x2715;</i></div>";
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
            "<option value='20'>Pequeño</option>"+
            "<option value='40'>Mediano</option>"+
            "<option value='60'>Grande</option>"+
            "</select>"+
            "<select class='form-select form-select-sm'>"+
            "<option value='ffffff'>Blanco</option>"+
            "<option value='333333'>Gris oscuro</option>"+
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
            "<input class='form-control form-control-sm' type='text' value='' placeholder='URL'>"+
            "<input class='form-control form-control-sm' type='text' value='' placeholder='Texto'>"+
            "<select class='form-select form-select-sm'>"+
            "<option value='white'>Blanco</option>"+
            "<option value='grey'>Gris oscuro</option>"+
            "</select> <i>&#x2715;</i></div>";
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
                "<option value='maquinas'>Máquinas Inteligentes y Conectadas</option>"+
                "<option value='redes'>Redes eléctricas digitales</option>"+
                "<option value='fabricacion'>Fabricación aditiva</option>"+
                "<option value='robotica'>Robótica flexible</option>"+
                "<option value='dispositivos'>Dispositivos médicos y salud digital</option>"+
                "<option value='materiales'>Materiales avanzados</option>"+
                "<option value='data'>Data Driven Solutions</option>"+
                "<option value='ciberseguridad'>Ciberseguridad</option>"+
                "<option value='caso'>Casos de exitos</option>"+
            "</select>"+
            "<i>&#x2715;</i>"+
            "</div>";
        if(jQuery(focusDiv).length) $(focusDiv).after(append);
        else $("#form").append(append);
        focusDiv = $("#item-"+item);
        item++;
        regeneratePreview();
    });

    $("#newbanner").click(function(e) {
        $("div").not(this).removeClass("focus");
        var append = "<div id='item-"+item+"' data-type='banner' class='focus col-12 bg-primary p-2 mb-2 pe-5'>"+
            "<select class='form-select form-select-sm'>"+
                "<option value='1'>Verde</option>"+
                "<option value='2'>Gris</option>"+
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

    $(document).on('change', "#form input, #form select, select#lang", function() {
        regeneratePreview();
    });

    $(document).on('change', "select#lang", function() {
        loadSaves();
        loadItems();
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
        focusDiv = null;
        $("#form").html('');
        var date = new Date().toJSON();
        $.getJSON( "{{ route('boletines.bdih-activos.saves') }}?load="+$("#loadfile").val(), function( data ) {
            $.each( data, function( key, val ) {
                var currentitem = item;
                $("#new"+val.type).click();
                if(val.value) {
                    $("#item-"+currentitem+" input, #item-"+currentitem+" select, #item-"+currentitem+" textarea").each(function(index) {
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