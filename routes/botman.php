<?php
use App\Http\Controllers\BotManController;
use App\{Cancion,Genero};

$botman = resolve('botman');

$botman->hears('Hi|hello|hola|Hello|Hola', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');

////////////////////////////////////////////////////////////////////////////
$botman->fallback(function ($bot) {
    $bot->reply("No entiendo que quieres decir, vuelve a intentarlo.");
});


$botman->hears('/ayuda', function ($bot) {
    $ayuda = ['/ayuda' => 'Mostrar este mensaje de ayuda',
    		   'administrar'=> 'Administrar el sistema',
              'acerca de|acerca' => 'Ver la información quien desarrollo este bot',
              'listar canciones|listar' => 'Listar las canciones disponibles',
              'crear playlist|playlist' => 'escoger canciones para la playlist',
              'mi lista' => 'Ver lista de canciones'];
    a
    $bot->reply("Los comandos disponibles son:");

    foreach($ayuda as $key => $value)
    {
            $bot->reply($key . ": " . $value);
    }
});

$botman->hears('administrar', BotManController::class.'@administrar');

$botman->hears('crear playlist|playlist', BotManController::class.'@cliente');


$botman->hears('listar canciones|listar', function($bot){
	$canciones=Cancion::all();
    foreach($canciones as $key => $value)
    {
        $bot->reply(($key+1).$value->infoSong());
    }
});

$botman->hears('acerca de|acerca', function ($bot) {
    $msj = "Este bot de charla fue desarrollado por:\n".
            "Jorge Andres llanos <jorgea.llanoc@autonoma.edu.co>\n".
            "Fabian Lopez <@autonoma.edu.co>\n".
            "Islen <@autonoma.edu.co>\n".
            "Desarrollado para metodos agiles del desarollo del software\n".
            "de la Maestria de gestion y desarollo de proyectos de software.";

    $bot->reply($msj);
});

