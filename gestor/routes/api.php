<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\Message;
use Gwannon\PHPActiveCampaignAPI\curlAC;

define('AC_API_DOMAIN', env('AC_API_DOMAIN', 'null')); //URL de la API de Active Campaign
define('AC_API_TOKEN', env('AC_API_TOKEN', 'null')); //Token de la API de Active Campaign
define('AC_LOG_API_CALLS', env('AC_LOG_API_CALLS', false)); //Guardar log de los llamadas a la API

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/boletines/been-basquetrade/saves/', function (Request $request) {
    if(isset($request->load) && $request->load != '') {
        $file = storage_path()."/saves/basquetrade/".$request->load;
        if(File::exists($file)) {
            return File::get($file);
        }
    }
    $dir = storage_path()."/saves/basquetrade/";
    $ignored = array('.', '..', '.svn', '.htaccess');
    $files = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }
    arsort($files);
    $files = array_keys($files);
    $files = array_diff($files, [".", "..", "index.php"]);
    return $files;
})->name('boletines.been-basquetrade.saves');

Route::post('/boletines/been-basquetrade/ajax/', function (Request $request) {
    $json = [];
    if(isset($request->action) && ($request->action == 'generate' || $request->action == 'send')) {
        $path = resource_path() . '/mail-templates/basquetrade/main.html';
        $html = File::get($path);
        $innerhtml = "";
        $currentcolor = "ffffff";
        $currenttitle = 0;
        if(isset($_POST['form'])) {
            foreach ($_POST['form'] as $item) {
                if($item['type'] == 'separator') {
                    $innerhtml .= File::get(resource_path() . "/mail-templates/basquetrade/separator.html");
                    $innerhtml = str_replace('[color]', $currentcolor, $innerhtml);
                } else if($item['type'] == 'spaciator') {
                    $innerhtml .= File::get(resource_path() . "/mail-templates/basquetrade/spaciator.html");
                    $innerhtml = str_replace('[color]', $item['value'][0], $innerhtml);
                    $innerhtml = str_replace('[size]', $item['value'][1], $innerhtml);
                } else if($item['type'] == 'title') {
                    $innerhtml .= File::get(resource_path() . "/mail-templates/basquetrade/title-".$item['value'][0].".html");
                    $currentcolor = "ffffff";
                    $currenttitle = $item['value'][0];
                } else if($item['type'] == 'item') {
                    if($currenttitle == 2)  $temp = File::get(resource_path() . "/mail-templates/basquetrade/event.html");
                    else $temp = File::get(resource_path() . "/mail-templates/basquetrade/item.html");
                    $temp = str_replace('[title]', $item['value'][0], $temp);
                    $temp = str_replace('[url]', $item['value'][4], $temp);
                    $temp = str_replace('[description]', $item['value'][5], $temp);
                    if($item['value'][1] != '') {
                      $temp = str_replace('[has_subtitle1]', "", $temp);
                      $temp = str_replace('[/has_subtitle1]', "", $temp);
                      $temp = str_replace('[subtitle1]', $item['value'][1], $temp);
                    } else {
                      $temp = str_replace('[has_subtitle1]', "<!-- ", $temp);
                      $temp = str_replace('[/has_subtitle1]', " -->", $temp);
                    }
                    if($item['value'][2] != '') {
                      $temp = str_replace('[has_subtitle2]', "", $temp);
                      $temp = str_replace('[/has_subtitle2]', "", $temp);
                      $temp = str_replace('[subtitle2]', $item['value'][2], $temp);
                    } else {
                      $temp = str_replace('[has_subtitle2]', "<!-- ", $temp);
                      $temp = str_replace('[/has_subtitle2]', " -->", $temp);
                    }
                    if($item['value'][3] != '') {
                      $temp = str_replace('[has_subtitle3]', "", $temp);
                      $temp = str_replace('[/has_subtitle3]', "", $temp);
                      $temp = str_replace('[subtitle3]', $item['value'][3], $temp);
                    } else {
                      $temp = str_replace('[has_subtitle3]', "<!-- ", $temp);
                      $temp = str_replace('[/has_subtitle3]', " -->", $temp);
                    }
                    $temp = str_replace('[color]', $item['value'][6], $temp);
                    $currentcolor = $item['value'][6];
                    $innerhtml .= $temp;
                }
            }
        }
        $html = str_replace("[MAIN]", $innerhtml, $html);
        File::put(base_path()."/public/temp/basquetrade-temp.html", $html);
        if($request->action == 'send') {
            $file = date("Y-m-d_H_i_s").".html";
            File::put(base_path()."/public/html/basquetrade/".$file, $html);
            foreach(explode(",", $_POST['email']) as $email) {
                $email = chop($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $title = "PRUEBA: ¿Cúal te interesa? Selección semanal de Oportunidades Comerciales en Europa";
                    $date = date("Y-m-d H:i");
                    $actual_link = request()->getSchemeAndHttpHost();
                    $content = File::get(base_path()."/public/html/basquetrade/".$file)."<br/><br><a href='".$actual_link."/html/basquetrade/".$file."'>DESCARGAR</a>";
                    $content = str_replace("%SENDER-INFO-SINGLELINE%", "SPRI – Agencia Vasca de Desarrollo Empresarial, Alameda Urquijo, 36 - 4ª Plta., Edificio Plaza Bizkaia, 48011 BILBAO, Bi, España ", $content);
                    $mail = new PHPMailer;
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host = env('MAIL_HOST');
                    $mail->SMTPAuth = true;
                    $mail->CharSet = 'UTF-8';
                    $mail->Username = env('MAIL_USERNAME');
                    $mail->Password = env('MAIL_PASSWORD');
                    $mail->SMTPSecure = env('MAIL_ENCRYPTION');
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->Port = env('MAIL_PORT');
                    $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $mail->isHTML(true);
                    $mail->Subject = $title;
                    $mail->MsgHTML($content);
                    $mail->AddAddress($email);
                    if(!$mail->Send())  $json = ['status' => 'danger', 'text' => 'No se ha podido enviar la newsletter. Inténtelo más tarde.'];
                } else if(!isset($json['status'])) $json = ['status' => 'danger', 'text' => 'Email incorrecto "'.$email.'".'];
            }
            if(!isset($json['status'])) $json = ['status' => 'success', 'text' => 'Newsletter enviada correctamente a: '.$_POST['email']];
        }
   } else if(isset($request->action) && $request->action == 'save' && isset($request->form)) {
        File::put(storage_path()."/saves/basquetrade/".(isset($request->namesave) && $request->namesave != '' ? str_replace(["/"], "-", $request->namesave) : 'Guardado').".json", json_encode($_POST['form']));
        if(!isset($json['status'])) $json = ['status' => 'success', 'text' => 'Newsletter guardada correctamente.'];
   }
   return $json;
})->name('boletines.been-basquetrade.ajax.post');

Route::get('/boletines/bdih-activos/items/', function (Request $request) {
    $json = [];
    $lang = strip_tags($request->lang);
    $items = json_decode(file_get_contents("https://bdih.spri.eus/".($lang != 'eu' ? $lang."/" : "").
        "wp-json/wp/v2/posts?_embed&per_page=100"));
        foreach ($items as $item) {
        if(isset($item->_embedded->{'wp:featuredmedia'}[0])) {
            $featuredimage = $item->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->full->source_url;
        } else $featuredimage = "";
        $json[] = [
            'type' => 'Noticia',
            'id' => $item->id,
            "timestamp" => strtotime($item->date),
            "date" => date("Y-m-d", strtotime($item->date)),
            'title' => $item->title->rendered,
            'url' => $item->link,
            'image' => $featuredimage,
            'description' => strip_tags($item->excerpt->rendered),
        ];
    }

    $items = json_decode(file_get_contents("https://www.spri.eus/ejson/casos-uso/?lang=".$lang.
    "&per_page=100&token=".env('WP_SPRI_API_TOKEN', 'null')));
    foreach ($items as $item) {
        list($dia, $mes, $ano) = explode("/", $item->fecha_caso);
        $json[] = [
            'type' => 'Caso de uso',
            'id' => $item->id,
            "timestamp" => strtotime($ano."/".$mes."/".$dia),
            "date" =>  date("Y-m-d", strtotime($ano."/".$mes."/".$dia)),
            'title' => $item->title,
            'url' => ($lang == 'es' ? "https://bdih.spri.eus/es/casos-de-uso/" : "https://bdih.spri.eus/erabilera-kasuak/").basename($item->url_spri),
            'image' => $item->img,
            'description' => strip_tags($item->extracto),
        ];
    }
    $keys = array_column($json, 'timestamp');
    array_multisort($keys, SORT_DESC, $json);
    
    return $json;
})->name('boletines.bdih-activos.items');


Route::get('/boletines/bdih-activos/saves/', function (Request $request) {
    if(isset($request->load) && $request->load != '') {
        $file = storage_path()."/saves/bdih-activos/".$request->load;
        if(File::exists($file)) {
            return File::get($file);
        }
    }
    $dir = storage_path()."/saves/bdih-activos/";
    $ignored = array('.', '..', '.svn', '.htaccess');
    $files = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }
    arsort($files);
    $files = array_keys($files);
    $files = array_diff($files, [".", "..", "index.php"]);
    return $files;
})->name('boletines.bdih-activos.saves');

Route::post('/boletines/bdih-activos/ajax/', function (Request $request) {
    $json = [];
    if(isset($request->action) && ($request->action == 'generate' || $request->action == 'send')) {
        $lang = strip_tags($request->lang);
        $path = resource_path() . '/mail-templates/activos-bdih/main_'.$lang.'.html';
        $html = File::get($path);
        //$html = file_get_contents("templates/main_".$lang.".html");
        $innerhtml = "";
        if(isset($request->form)) {
        foreach ($request->form as $item) {
            if($item['type'] == 'button') {
                $innerhtml .= File::get(resource_path() . "/mail-templates/activos-bdih/button_".$item['value'][2].".html");
                $innerhtml = str_replace('[url]', $item['value'][0], $innerhtml);
                $innerhtml = str_replace('[texto]', $item['value'][1], $innerhtml);
            } else if($item['type'] == 'image') {
                $innerhtml .= File::get(resource_path() . "/mail-templates/activos-bdih/image.html");
                $innerhtml = str_replace('[image]', $item['value'][0], $innerhtml);
            } else if($item['type'] == 'spaciator') {
                $innerhtml .= File::get(resource_path() . "/mail-templates/activos-bdih/spaciator.html");
                $innerhtml = str_replace('[size]', $item['value'][0], $innerhtml);
                $innerhtml = str_replace('[color]', $item['value'][1], $innerhtml);
            } else if($item['type'] == 'title') {
                $innerhtml .= File::get(resource_path() . "/mail-templates/activos-bdih/title-".$item['value'][0]."_".$lang.".html");
            } else if($item['type'] == 'item') {
                if($item['value'][5] == 'featured')  $temp = File::get(resource_path() . "/mail-templates/activos-bdih/featureditem_".$lang.".html");
                else if($item['value'][5] == 'event')  $temp = File::get(resource_path() . "/mail-templates/activos-bdih/event_".$lang.".html");
                else if($item['value'][5] == 'case')  $temp = File::get(resource_path() . "/mail-templates/activos-bdih/case_".$lang.".html");
                else $temp = $temp = File::get(resource_path() . "/mail-templates/activos-bdih/item_".$lang.".html");
                $temp = str_replace('[title]', $item['value'][0], $temp);
                if($item['value'][1] != '') {
                    $temp = str_replace('[has_subtitle]', "", $temp);
                    $temp = str_replace('[/has_subtitle]', "", $temp);
                    $temp = str_replace('[subtitle]', $item['value'][1], $temp);
                } else {
                    $temp = str_replace('[has_subtitle]', "<!-- ", $temp);
                    $temp = str_replace('[/has_subtitle]', " -->", $temp);
                }
                $temp = str_replace('[imagen_url]', $item['value'][2], $temp);
                $temp = str_replace('[url]', $item['value'][3], $temp);
                $temp = str_replace('[description]', $item['value'][4], $temp);
                $innerhtml .= $temp;
            } else if($item['type'] == 'banner') {
                $innerhtml .= File::get(resource_path() . "/mail-templates/activos-bdih/banner-".$item['value'][0]."_".$lang.".html");
            }
        }
        }
        $html = str_replace("[MAIN]", $innerhtml, $html);
        File::put(base_path()."/public/temp/bdih-activos.html", $html);
        if($request->action == 'send') {

            $file = date("Y-m-d_H_i_s").".html";
            File::put(base_path()."/public/html/bdih-activos/".$file, $html);
            foreach(explode(",", $request->email) as $email) {
                $email = chop($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    //if(!sendTest($email, "Activos Tecnológicos BDIH. Antes de invertir en tecnología...", $file)) $json = ['status' => 'danger', 'text' => 'NO se ha podido enviar la newsletter. Inténtelo más tarde.'];
                    $title = ( $lang == 'es' ? "PRUEBA: Activos Tecnológicos BDIH. Antes de invertir en tecnología..." : "PRUEBA: BDIH aktibo teknologikoak. Teknologian inbertitu aurretik...");
                    $date = date("Y-m-d H:i");
                    $actual_link = request()->getSchemeAndHttpHost();
                    $content = File::get(base_path()."/public/html/bdih-activos/".$file)."<br/><br><a href='".$actual_link."/html/bdih-activos/".$file."'>DESCARGAR</a>";
                    $content = str_replace("%SENDER-INFO-SINGLELINE%", "SPRI – Agencia Vasca de Desarrollo Empresarial, Alameda Urquijo, 36 - 4ª Plta., Edificio Plaza Bizkaia, 48011 BILBAO, Bi, España ", $content);
                    $mail = new PHPMailer;
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host = env('MAIL_HOST');
                    $mail->SMTPAuth = true;
                    $mail->CharSet = 'UTF-8';
                    $mail->Username = env('MAIL_USERNAME');
                    $mail->Password = env('MAIL_PASSWORD');
                    $mail->SMTPSecure = env('MAIL_ENCRYPTION');
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->Port = env('MAIL_PORT');
                    $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $mail->isHTML(true);
                    $mail->Subject = $title;
                    $mail->MsgHTML($content);
                    $mail->AddAddress($email);
                    if(!$mail->Send())  $json = ['status' => 'danger', 'text' => 'No se ha podido enviar la newsletter. Inténtelo más tarde.'];
                } else if(!isset($json['status'])) $json = ['status' => 'danger', 'text' => 'Email incorrecto "'.$email.'".'];
            }
            
            if(!isset($json['status'])) $json = ['status' => 'success', 'text' => 'Newsletter enviada correctamente: '.$_POST['email']];
        }
    } else if(isset($request->action) && $request->action == 'save' && isset($request->form)) {
        File::put(storage_path()."/saves/bdih-activos/".(isset($request->namesave) && $request->namesave != '' ? str_replace(["/"], "-", $request->namesave) : 'Guardado').".json", json_encode($request->form));
        if(!isset($json['status'])) $json = ['status' => 'success', 'text' => 'Newsletter guardada correctamente.'];
    }
    return $json;
})->name('boletines.bdih-activos.ajax.post');

Route::get('/info/view/ajax', function (Request $request) {
    $exclude_urls = [
        "https://www.spri.eus/es/",
        "https://www.spri.eus/",
        "https://www.youtube.com/user/grupoSPRI",
        "https://www.linkedin.com/company/grupospri",
        "https://twitter.com/grupospri",
        "https://www.spri.eus/es/rss-2/",
        "https://www.spri.eus/es/personaliza-tus-boletines/",
        "https://www.spri.eus/es/preferencias-de-tus-suscripciones/?wpatg_tab=login",
        "https://www.spri.eus/es/preferencias-de-tus-suscripciones/?wpatg_tab=register",
        "https://www.spri.eus/es/preferencias-de-tus-suscripciones/?wpatg_tab=login",
        "https://www.spri.eus/es/preferencias-de-tus-suscripciones/?wpatg_tab=register",
        "https://www.spri.eus/es/boletines/",
        "https://www.spri.eus/es/aviso-legal-2/",
        "https://www.spri.eus/es/aviso-legal/",
        "https://www.spri.eus/pertsonalizatu-zure-buletinak/",
        "https://www.spri.eus/buletinak/",
        "https://www.spri.eus/lege-oharra/",
        "https://www.eenbasque.net/"
    ];

    $items = array();
    $campaign = curlAC::curlCall("/campaigns/".$request->campaign_id)->campaign;
    $items['uniquelinkclicks'] = intval($campaign->uniquelinkclicks);
    $items['linkclicks'] = intval($campaign->linkclicks);
    $links = curlAC::curlCall("/campaigns/".$request->campaign_id."/links")->links;

    //Quitamos del número de clicks los excluidos
    foreach ($links as $link) { 
        if (filter_var($link->link, FILTER_VALIDATE_URL) && in_array($link->link, $exclude_urls)) {
            $items['uniquelinkclicks'] = $items['uniquelinkclicks'] - intval($link->uniquelinkclicks);
            $items['linkclicks'] = $items['linkclicks'] - intval($link->linkclicks);
        }
    }
    foreach ($links as $link) { 
        if (filter_var($link->link, FILTER_VALIDATE_URL) && !in_array($link->link, $exclude_urls)) {
            //print_r ($link);
            $items['links'][] = [
                "link" => $link->link,
                "uniquelinkclicks" => intval($link->uniquelinkclicks),
                "uniquelinkclickspercent" => round(((intval($link->uniquelinkclicks) / $items['uniquelinkclicks']) * 100), 2),
                "linkclicks" => (intval($link->linkclicks) != "" ? intval($link->linkclicks) : 0), 
                "linkclickspercent" => round(((intval($link->linkclicks) / $items['linkclicks']) * 100), 2),
            ];
        }
    }
    if (count($items['links']) == 0) return false;
    return $items;
})->name('info-view.ajax');

Route::get('/info/ajax/', function (Request $request) {
    $items = [];
    $offset = 0;
    $campaigns = curlAC::curlCall("/campaigns?orders[sdate]=DESC&offset=".$request->offset."&limit=".env('AC_API_LIMIT', 20))->campaigns;
    foreach ($campaigns as $campaign) { 
        $messages = Message::where('campaign_id', $campaign->id)->get();
        if(sizeof($messages) == 0){
            $messages = curlAC::curlCall(str_replace(AC_API_DOMAIN, "", $campaign->links->campaignMessages))->campaignMessages;
            //print_r($messages); die;
            $subject = $messages[0]->subject;
            $html = curlAC::curlCall(str_replace(AC_API_DOMAIN, "", $messages[0]->links->message)); //Conseguimos el texto de la campaña
            $image = $messages[0]->screenshot;
            //Guardamos en base de datos
            $newmessage = new Message;
            $newmessage->campaign_id = $campaign->id;
            $newmessage->title = $subject;
            $newmessage->titleab = '';
            $newmessage->text = $html->message->html;
            $newmessage->textab = '';
            $newmessage->image = $image;
            $newmessage->save();

        } else {
            $subject = $messages[0]->title;
            $html = $messages[0]->text;
            $image = $messages[0]->image;
            //return array("title" => $row['title'], "titleab" => $row['titleab'],"image" => $row['image'], "text" => $row['text'], "textab" => $row['textab']);
        }

        $items[] = [
            "id" => $campaign->id,
            "type" => $campaign->type,
            "date" => date("Y-m-d H:i", strtotime($campaign->sdate)),
            "name" => $campaign->name,
            "subject" => $subject,
            "send_amt" => $campaign->send_amt,
            "uniqueopens" => $campaign->uniqueopens,
            "uniqueopens_percent" => number_format(($campaign->uniqueopens > 0 && $campaign->send_amt > 0 ? round(($campaign->uniqueopens / $campaign->send_amt * 100), 2) : 0), 2, ",", "."),
            "opens" => $campaign->opens,
            "uniquelinkclicks" => $campaign->uniquelinkclicks,
            "uniquelinkclicks_percent" => number_format(($campaign->uniquelinkclicks > 0 && $campaign->send_amt > 0 ? round(($campaign->uniquelinkclicks / $campaign->send_amt * 100), 2) : 0), 2, ",", "."),
            "linkclicks" => $campaign->linkclicks,
            "unsubscribes" => $campaign->unsubscribes,
            "image" => $image,
            "segment_name" => $campaign->segmentname,
            "hash" => md5($campaign->name)
          ];
    }
    if (count($items) == 0) return [];
    return $items;
})->name('info.ajax');
