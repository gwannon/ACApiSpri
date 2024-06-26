<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\Statistic;
use App\Models\Link;
use App\Http\Controllers\LanguageController;
use Gwannon\PHPActiveCampaignAPI\curlAC;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

define('PERMS', "boletin-basquetrade,boletin-bdih-activos,boletines-spri,info-campanas,info-adimedia,admin-usuarios,admin-otros-paneles,otros-paneles");

Route::get('lang/{lang}', [LanguageController::class, 'swap'])->name('lang.swap');
Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', function () {
        $links = Link::orderBy('title', 'asc')->get();
        return view('home', 
        ['links' => $links]);
    })->name('home');

    //Paneles de información
    Route::get('/info/view/{id}', function ($id) {

        $message = Message::where('campaign_id', $id)->first();
        $message['text'] = str_replace(["</html>", "</body>"], "", $message['text']);

        return view('info-campana-view', 
            ['message' => $message]
        );
    })->name('info.campanas.view');

    Route::get('/info/registrados-ultimo-mes', function () {
        $data = [];
        $date = date("Y-m-d", strtotime("1 month ago"));
        $tagIDs = [];
        $response = curlAC::curlCall("/tags?limit=100&search=via-");
        foreach($response->tags as $tag) {
            $tagIDs[$tag->id] = $tag->tag;
        }
        $response = curlAC::curlCall("/tags?limit=100&search=DOC-");
        foreach($response->tags as $tag) {
            $tagIDs[$tag->id] = $tag->tag;
        }
        $limit = 100;
        $offset = 0;
        $total = 0;
        $counter = 1;
        while (1 == 1) {
            $response = curlAC::curlCall("/contacts?listid=17&status=1&orders[cdate]=DESC&filters[created_after]=".$date."&limit=".$limit."&offset=".$offset);
            $offset = $offset + $limit;
            if (!isset($response->contacts) || count($response->contacts) == 0) break;
            foreach($response->contacts as $contact) {
                $counter++;
                $response_tags = curlAC::curlCall(str_replace(AC_API_DOMAIN, "", $contact->links->contactTags));
                if(isset($response_tags->contactTags)) {
                    foreach($response_tags->contactTags as $tag) {
                        
                        if(array_key_exists($tag->tag, $tagIDs)) {
                            $total++;
                            if(!isset($data[$tag->tag])) {
                                $data[$tag->tag] = [
                                    "name" => $tagIDs[$tag->tag],
                                    "total" => 1
                                ];
                            } else {
                                $data[$tag->tag]['total']++;
                            }
                            break;
                        }
                    }
                }
            }
        }
        return view('info-registrados-ultimo-mes', ["data" => $data, "total" => $total]);
    })->name('info.registrados-ultimo-mes');

    Route::get('/info/apuntados-boletines', function () {
        $data = [];
        $tags = [
            "newsletter-grupospri" => "Grupo SPRI (Lunes)",
            "newsletter-grupospri-empresa" => "Grupo SPRI Empresas (Martes)",
            "newsletter-been" => "BEEN comercial (Miercoles)",
            "newsletter-adiagenda" => "ADI! (Jueves)",
            "newsletter-empresadigitala" => "Mikroempresa Digitala (Viernes)",
            "newsletter-upeuskadi" => "UPEuskadi (Mensual)",
            "newsletter-bdih" => "BDIH Activos (Quincenal)",
            "newsletter-been-comercial" => "BEEN técnico (Miercoles)",
        ];
        $tagIDs = [];
        $response = curlAC::curlCall("/tags?limit=100&offset=0&search=newsletter-");
        foreach($response->tags as $tag) {
            if(array_key_exists($tag->tag, $tags)) {
                $tagIDs[$tag->id] = $tag->tag;
            }
        }
        foreach($tagIDs as $tagID => $tagLabel) {
            $response = curlAC::curlCall("/contacts?listid=17&status=1&tagid=".$tagID);
            $data[] = [ 
                "name" =>$tags[$tagLabel],
                "total" => $response->meta->total
            ];
        }
        $response = curlAC::curlCall("/contacts?listid=17&status=1&segmentid=1743");
        $total = $response->meta->total;
        return view('info-apuntados-boletines', ["data" => $data, "total" => $total]);
    })->name('info.apuntados-boletines');

    Route::get('/info/automatizaciones/todas', function () {
        $data = [];
        $offset = 0;
        $max = 100;
        $automs_api = curlAC::curlCall("/automations?orders[name]=ASC&offset=".$offset."&limit=".$max)->automations; 
        while(count($automs_api) > 0) { 
            
            foreach ($automs_api as $current_autom) {
                $data[$current_autom->id] = [
                    "name" => $current_autom->name,
                    "screenshot" => $current_autom->screenshot,
                    "entered" => $current_autom->entered,
                    "exited" => $current_autom->exited,
                    "status" => $current_autom->status,
                ];
            } //if ($offset > 500) break;
            $offset = $offset + $max;
            $automs_api = curlAC::curlCall("/automations?orders[name]=ASC&offset=".$offset."&limit=".$max)->automations; 
        }
        return view('info-automatizaciones-todas', ["data" => $data]);
    })->name('info.automatizaciones-todas');

    Route::get('/info/automatizaciones', function () {
        $automs = [
            /*"Innobideak" => [
                "170" => 5,
                "169" => 10 
            ], */
            "Ciberseguridad" => [
                "108" => 0,
                "109" => 5,
                "110" => 10,
                "111" => 50,
                "107" => 100,
                "106" => 0
            ], 
            "Digitalización" => [
                "115" => 0,
                "116" => 5,
                "117" => 10,
                "118" => 50,
                "114" => 100,
                "113" => 0
            ], 
            "Emprendimiento" => [
                "121" => 0,
                "122" => 5,
                "123" => 10,
                "124" => 50,
                "120" => 100,
                "119" => 0
            ], 
            "Financiación" => [
                "127" => 0,
                "128" => 5,
                "129" => 10,
                "130" => 50,
                "126" => 100,
                "125" => 0
            ], 
            "Interés I+D" => [
                "133" => 0,
                "134" => 5,
                "135" => 10,
                "136" => 50,
                "132" => 100,
                "131" => 0
            ], 
            "Interés Infraestructuras" => [
                "139" => 0,
                "140" => 5,
                "141" => 10,
                "142" => 50,
                "138" => 100,
                "137" => 0
            ], 
            "Innovación" => [
                "145" => 0,
                "146" => 5,
                "147" => 10,
                "148" => 50,
                "144" => 100,
                "143" => 0
            ], 
            "Internacionalización" => [
                "151" => 0,
                "152" => 5,
                "153" => 10,
                "154" => 50,
                "150" => 100,
                "149" => 0
            ], 
            "Invest in Basque Country" => [
                "157" => 0,
                "158" => 5,
                "159" => 10,
                "160" => 50,
                "156" => 100,
                "155" => 0
            ], 
            "Interés Sostenibilidad Medioambiental" => [
                "163" => 0,
                "164" => 5,
                "165" => 10,
                "166" => 50,
                "162" => 100,
                "161" => 0
            ],
        ];

        $automs_api = curlAC::curlCall("/automations?offset=0&limit=100")->automations; 

        $data = [];
        foreach ($automs as $label => $autom_ids) {
            $total_puntos = 0;
            $total_ejecuciones = 0;
            foreach ($autom_ids as $autom_id => $value) {
										
                foreach ($automs_api as $autom_api) {
                    if($autom_api->id == $autom_id) { 
                        $current_autom = $autom_api;
                        break;
                    }
                } 
                $total_ejecuciones = $total_ejecuciones + $current_autom->exited;
                $total_puntos = $total_puntos + ($current_autom->exited * $value);
                $data[$label][$current_autom->id] = [
                    "name" => $current_autom->name,
                    "exited" => $current_autom->exited,
                    "total" => $current_autom->exited*$value
                ];
            }
        }
        return view('info-automatizaciones', ["data" => $data, "total_ejecuciones" => $total_ejecuciones, "total_puntos" => $total_puntos]);
    })->name('info.automatizaciones');

    Route::get('/info/tags', function () {
        $tags = [
            "Engagement" => [
                "169", //engagement3m
                "170", //engagement6m
                "171", //disengaged
                "172", //inactive
            ], 
            /*"Basque Open Industry" => [
                "383", //inscrito-boi
                "386", //novedades-boi
                "381", //newsletter-boi
            ], */
            "Intereses" => [
                "98", //interes-ciberseguridad
                "101", //interes-digitalizacion
                "96", //interes-emprendimiento
                "105", //interes-financiacion
                "102", //interes-i+d
                "107", //interes-infraestructuras
                "97", //interes-innovacion
                "103", //interes-internacionalizacion
                "104", //interes-invertir-en-euskadi
                "106", //interes-sostenibilidad-ambiental
                "182", //Interes-todos
            ],
            "Boletines" => [
                "19", //newsletter-grupospri
                "323", //newsletter-grupospri-empresa
                "283", //newsletter-empresadigitala
                "21", //newsletter-adiagenda
                "20", //newsletter-upeuskadi
                "80", //newsletter-been
                "394", //newsletter-bdih
                "444", //newsletter-been-comercial
            ],
            /*"Intereses Boletines" => [
                "312", //interes-newsletter-ciberseguridad
                "313", //interes-newsletter-digitalizacion
                "314", //interes-newsletter-emprendimiento
                "315", //interes-newsletter-financiacion
                "316", //interes-newsletter-i+d
                "317", //interes-newsletter-infraestructuras
                "318", //interes-newsletter-innovacion
                "319", //interes-newsletter-internacionalizacion
                "320", //interes-newsletter-invertir-en-euskadi
                "321", //interes-newsletter-sostenibilidad-medioambiental
                "322" //interes-newsletter-todos
            ], */	
            "Notificaciones" => [
                "282", //notificar-ayudas
                "281", //notificar-documentacion
                "280", //notificar-eventos
            ],
            "Idiomas" => [
                "18", //newsletter-es
                "30", //newsletter-eu
            ],
        ];

        $data = [];
        foreach ($tags as $label => $tag_ids) {
            foreach ($tag_ids as $tag_id) {
                $tag = curlAC::curlCall("/tags/".$tag_id)->tag; 
                $data[$label][$tag_id] = [
                    "tag" => $tag->tag,
                    "susbcribers" => $tag->subscriber_count,
                    "date" => date("Y-m-d H:i:s", strtotime($tag->updated_timestamp))     
                ];
            }
        }
        return view('info-tags', ["data" => $data]);
    })->name('info.tags');

    Route::get('/info/usuarios/{days}', function ($days) {
        $data = [];
        $counter = 0;
        while ($counter < $days) {
            $statistic = Statistic::where('date', date("Y-m-d", strtotime("-".($counter+1)." days"))." 00:00:00")->first();
            if(isset($statistic->id) && $statistic->id > 0) {
                $counter ++;
                $label = date("m/d", strtotime("-".$counter." days"));
                $data[$label] =  $statistic->new_users;
            } else {
                $enddate = date("Y-m-d", strtotime("-".$counter." days"));
                $counter ++;
                $startdate = date("Y-m-d", strtotime("-".$counter." days"));
                $result = curlAC::curlCall("/contacts?filters[created_before]=".$enddate."&filters[created_after]=".$startdate)->meta->total;
                $label = date("m/d", strtotime("-".$counter." days"));
                $data[$label] =  $result;
                //Guardamos en base de datos el dato
                $statistic = new Statistic();
                $statistic->date = date("Y-m-d", strtotime("-".$counter." days"));
                $statistic->new_users =  $result;
                $statistic->save();
            }
        }
        $data = array_reverse($data);
        return view('info-usuarios', ["data" => $data, "days" => $days]);
    })->name('info.usuarios');


    Route::get('/info/leadscorings/{type}', function ($type) {


        $segments = [
            "intereses" => [
                "583" => "Ciberseguridad",
                "596" => "Digitalización", 
                "589" => "Emprendimiento",
                "590" => "Financiación",
                "591" => "I+D",
                "592" => "Infraestructuras",
                "581" => "Innovación",
                "594" => "Internacionalización",
                "593" => "Invest In Basque Country",
                "595" => "Sostenibilidad medioambiental"
            ], 
            "ayudas" => [
                "1264" => "5G empresarial",
                "853" => "Aholku",
                "729" => "Atracción de proveedores estratégicos",
                "788" => "Aurrera",
                "1037" => "Azpitek",
                "1590" => "Banda Ancha Ultrarrápida",
                "976" => "Barnekintzaile",
                "1313" => "Barnetegi Teknologiko",
                "645" => "Baskeep",
                "973" => "Basque Fondo",
                "1372" => "Basque Tek Ventures",
                "1036" => "Bateratu",
                "685" => "BDIH + Deep Dives BDIH",
                "1312" => "BDIH Konexio",
                "778" => "Becas Beint",
                "1583" => "Becas Global Training", 
                "686" => "BEEN",
                "1091" => "Bideratu Berria",
                "1125" => "Bilakatu",
                "696" => "BIND 4.0",
                "697" => "BIND 4.0 SME Connection",
                "1608" => "BOI (Menos inscritos)",
                "866" => "Bultzatu",
                "1586" => "Certificaciones BAIT / IT Txartela",
                "734" => "Ciberseguridad Industrial",
                "1052" => "Competencias Digitales Profesionales",
                "975" => "Ekintzaile",
                "880" => "Elkartek",
                "1070" => "Elkartu",
                "1035" => "Emaitek Plus",
                "1588" => "Energías más limpias",
                "1288" => "Enpresa Digitala",
                "834" => "Gauzatu Industria",
                "1221" => "Gauzatu Internacional",
                "715" => "Hablamos de tí",
                "653" => "Hazinnova",
                "879" => "Hazitek",
                "1592" => "Impulsando El Euskera",
                "898" => "Indartu",
                "1371" => "Industria Digitala",
                "1276" => "Industria Inteligente",
                "597" => "Innobideak Innokonexio",
                "1388" => "Innobideak Prestakuntza",
                "1582" => "Inteligencia Artificial Aplicada",
                "1415" => "Inteligencia Competitiva",
                "958" => "Invest in the Basque Country",
                "1581" => "Kloud",
                "1110" => "Lortu",
                "1077" => "Mikroenpresa digitala",
                "765" => "Mujeres en la industria",
                "1158" => "Net-Zero",
                "870" => "Pilotu",
                "1160" => "Renove Industria 4.0",
                "1106" => "Sakondu",
                "1591" => "Servicio de Emisión de Informes Técnicos de Calificación a Efectos Fiscales",
                "1589" => "Servicios de apoyo a la internacionalización",
                "1347" => "Smart Industry",
                "974" => "UP Euskadi",
                "1109" => "Zabaldu",
            ], 
        ];

        $data = [];

        foreach ($segments[$type] as $segment_id => $segment_name) {
            $segment = curlAC::curlCall("/contacts?segmentid=".$segment_id); 
            $data[$segment_id] = [
                "name" => $segment_name,
                "susbcribers" => $segment->meta->total, 
            ];
        }
        return view('info-leadscorings', ["data" => $data, "type" => $type]);
    })->name('info.leadscorings');

    Route::get('/info', function () {
        return view('info-campanas');
    })->name('info.campanas');

    //Boletines --------------------------
    Route::get('/boletines/been-basquetrade', function () {
        return view('boletin-basquetrade');
    })->name('boletines.been-basquetrade');

    Route::get('/boletines/bdih-activos', function () {
        return view('boletin-bdih-activos');
    })->name('boletines.bdih-activos');

    //Administración ---------------------
    Route::get('/admin/usuarios/borrar/{id}', function ($id) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        if($res=User::where('id',$id)->delete()) return redirect(route('admin.usuarios', ['delete' => 'ok']).'#users');
        else return redirect(route('admin.usuarios', ['delete' => 'error']).'#users');
    })->name('admin.usuarios.borrar');

    Route::post('/admin/usuarios/editar/{id}', function ($id, Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/');
        User::where('id', $id)->update([ 
            'name' => $request->input('username'),
            'email' => $request->input('useremail'),
            'perms' => implode(",", $request->input('userperms')),
        ]);
        if($request->input('userpassword') != '' && strlen($request->input('userpassword')) >= 8 ) {
            User::where('id', $id)->update([
                'password' => Hash::make($request->input('userpassword'))
            ]);
        }
        return redirect(route('admin.usuarios.editar',  ["id" => $id, 'edit' => 'ok']));
    })->name('admin.usuarios.editar');

    Route::get('/admin/usuarios/editar/{id}', function ($id) {
        //if(Auth::user()->superadmin != 1) return redirect('/');
        $perms = explode(",", PERMS);
        $user = User::where('id', $id)->first();
        return view('admin_user_edit', [
            'user' => $user,
            'perms' => $perms
        ]);
    })->name('admin.usuarios.editar');

    Route::post('/admin/usuarios', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $perms = explode(",", PERMS);
        $user_created = false;
        $users = User::where('email', $request->input('useremail'))->get();
        if(sizeof($users) == 0){
            $dataUser = new User();
            $dataUser->name = $request->input('username');
            $dataUser->password = Hash::make($request->input('userpassword'));
            $dataUser->email = $request->input('useremail');
            $dataUser->perms = implode(",", $request->input('userperms'));
            $dataUser->save();
            $user_created = true;
        }
        $users = User::orderBy('name', 'asc')->get();
        return view('admin_users', [
            'user_created' => $user_created,
            'users' => $users,
            'perms' => $perms
        ]);
    })->name('admin.usuarios.crear');

    Route::get('/admin/usuarios', function (Request $request) {
        $perms = explode(",", PERMS);
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $users = User::orderBy('name', 'asc')->get();
        return view('admin_users', [
            'users' => $users,
            'perms' => $perms
        ]);
    })->name('admin.usuarios');



    Route::get('/admin/otrospaneles/borrar/{id}', function ($id) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        if($res=Link::where('id',$id)->delete()) return redirect(route('admin.otrospaneles', ['delete' => 'ok']).'#links');
        else return redirect(route('admin.otrospaneles', ['delete' => 'error']).'#links');
    })->name('admin.otrospaneles.borrar');

    Route::post('/admin/otrospaneles', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $link_created = false;
        $links = Link::where('url', $request->input('linkurl'))->get();
        if(sizeof($links) == 0){
            $dataLink = new Link();
            $dataLink->title = $request->input('linktitle');
            $dataLink->url = $request->input('linkurl');
            $dataLink->save();
            $link_created = true;
        }
        $links = Link::orderBy('title', 'asc')->get();
        return view('admin_links', [
            'link_created' => $link_created,
            'links' => $links
        ]);
    })->name('admin.otrospaneles.crear');

    Route::get('/admin/otrospaneles', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $links = Link::orderBy('title', 'asc')->get();
        return view('admin_links', [
            'links' => $links
        ]);
    })->name('admin.otrospaneles');

    Route::get('/change-password', [App\Http\Controllers\HomeController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('update-password');
});

Route::get('/', function (Request $request) {
    return redirect('/login');
})->name('user.home');

Auth::routes(['register' => false]);
