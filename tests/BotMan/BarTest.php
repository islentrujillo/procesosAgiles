<?php

namespace Tests\BotMan;

use Illuminate\Foundation\Inspiring;
use Tests\TestCase;

class BarTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testBasicTest()
    {
        $msj = "Este bot de charla fue desarrollado por:\n".
            "Jorge Andres llanos <jorgea.llanoc@autonoma.edu.co>\n".
            "Fabian Lopez <fabiana.lopezc@autonoma.edu.co>\n".
            "Islen Trujillo islentrujillo@gmail.com>\n".
            "Desarrollado para metodos agiles del desarollo del software\n".
            "de la Maestria de gestion y desarollo de proyectos de software.";
        $this->bot->receives('acerca')
            ->assertReply($msj);
    } 

    public function testNoneTest(){
        $this->bot->receives('mal para')
            ->assertReply("No entiendo que quieres decir, vuelve a intentarlo.");
    }

    public function testPlaylistTest(){
      $this->bot->receives('playlist')
        ->assertQuestion('Las opciones disponibles son: ')
        ->receivesInteractiveMessage('ver_playlists')
        ->assertQuestion('Selecciona una Lista');
        // ->receivesInteractiveMessage('ver_playlists')
        // ->assertReply();
    }
}
