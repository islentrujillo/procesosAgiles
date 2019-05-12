<?php
use App\Http\Controllers\BotManController;
// use BotMan\BotMan\Messages\Incoming\Answer;
// use BotMan\BotMan\Messages\Outgoing\Question;
// use BotMan\BotMan\Messages\Outgoing\Actions\Button;
// use Illuminate\Foundation\Inspiring;

// use BotMan\BotMan\Messages\Conversations\Conversation;


use App\{Cancion,Genero};


$botman = resolve('botman');

//$usuario=auth::user->name;

//$botman->hears('Hi|hello|hola|Hello|Hola', function ($bot) {
    //$bot->reply("Hola Bienvenido a Nuestro BAR - MASTER! para conoser las opciones escribe ayuda");
    $botman->hears("hola", function ($bot) { $bot->reply("hola ".auth::user()->name);
}); 

// $botman->hears('Start conversation', BotManController::class.'@startConversation');

////////////////////////////////////////////////////////////////////////////
$botman->fallback(function ($bot) {
    $bot->reply("No entiendo que quieres decir, vuelve a intentarlo.");
});


$botman->hears('/ayuda|ayuda', function ($bot) {
  if(Auth::user()->email=='barbot@gmail.com'){ 
    $saludo="Bienvedio usuario ADMINISTRADOR MENTIROSO a nuestro BAR MASTER! ara conocer sus opciones escribe la palabra ayuda";
    $ayuda = ['acerca de|acerca' => 'Ver la información de quien desarrollo este bot',
              'administrar|admin'=> 'Administrar el sistema',
             'listar canciones|listar' => 'Listar las canciones disponibles',
             'genero {genero}' => 'buscar canciones de un genero'];
    } 
    else{
        $saludo="Bienvedio usuario CLIENTE a nuestro BAR MASTER! para conocer sus opciones escribe la palabra ayuda";   
        $ayuda = ['acerca de|acerca' => 'Ver la información de quien desarrollo este bot',
              'listar canciones|listar' => 'Listar las canciones disponibles',
              'crear playlist|playlist' => 'administrar playlist',
              'genero {genero}' => 'buscar canciones de un genero'];
        }      
     $bot->reply($saludo);
    $bot->reply("Los comandos disponibles son:");

    foreach($ayuda as $key => $value)
    {
            $bot->reply($key . ": " . $value);
    }
});

$botman->hears('administrar|admin', BotManController::class.'@administrar');

$botman->hears('crear playlist|playlist', BotManController::class.'@cliente');

$botman->hears('listar canciones|listar', function($bot){
     $canciones=Cancion::all();
    foreach($canciones as $key => $value)
    {
        $bot->reply(($key+1).$value->infoSong());
    }
});

$botman->hears('genero {genre}', function($bot, $genre){
    $genero = Genero::where('nombre',ucwords($genre))->first();
    if($genero==null){
        $bot->reply('genero no encontrado');
    }else{
        foreach($genero->canciones as $key => $value)
        {
            $bot->reply(($key+1).$value->infoSong());
        }
    }
});

$botman->hears('acerca de|acerca', function ($bot) {
    $msj = "Este bot de charla fue desarrollado por:\n".
            "Jorge Andres llanos <jorgea.llanoc@autonoma.edu.co>\n".
            "Fabian Lopez <fabiana.lopezc@autonoma.edu.co>\n".
            "Islen Trujillo islentrujillo@gmail.com>\n".
            "Desarrollado para metodos agiles del desarollo del software\n".
            "de la Maestria de gestion y desarollo de proyectos de software.";

    $bot->reply($msj);
});