<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
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

Route::get('lang/{lang}', [LanguageController::class, 'swap'])->name('lang.swap');
Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    //Paneles de información
    Route::get('/info/view/{id}', function ($id) {

        $message = Message::where('campaign_id', $id)->first();
        $message['text'] = str_replace(["</html>", "</body>"], "", $message['text']);

        return view('info-campana-view', 
            ['message' => $message]
        );
    })->name('info.campanas.view');

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
        /*echo "<pre>";
        print_r($data);
        echo "</pre>";*/
        return view('info-tags', ["data" => $data]);
    })->name('info.tags');

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
            'perms' => $request->input('userperms'),
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
        $user = User::where('id', $id)->first();
        return view('admin_user_edit', [
            'user' => $user
        ]);
    })->name('admin.usuarios.editar');

    Route::post('/admin/usuarios', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $user_created = false;
        $users = User::where('email', $request->input('useremail'))->get();
        if(sizeof($users) == 0){
            $dataUser = new User();
            $dataUser->name = $request->input('username');
            $dataUser->password = Hash::make($request->input('userpassword'));
            $dataUser->email = $request->input('useremail');
            $dataUser->perms = $request->input('userperms');
            $dataUser->save();
            $user_created = true;
        }
        $users = User::orderBy('name', 'asc')->get();
        return view('admin_users', [
            'user_created' => $user_created,
            'users' => $users
        ]);
    })->name('admin.usuarios.crear');

    Route::get('/admin/usuarios', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $users = User::orderBy('name', 'asc')->get();
        return view('admin_users', [
            'users' => $users
        ]);
    })->name('admin.usuarios');

    Route::get('/change-password', [App\Http\Controllers\HomeController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('update-password');
});

Route::get('/', function (Request $request) {
    return redirect('/login');
})->name('user.home');

Auth::routes(['register' => false]);
