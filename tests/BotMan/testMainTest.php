<?php
namespace Tests\BotMan;
use Illuminate\Foundation\Inspiring;
use Tests\TestCase;
use App\{Genero,Cancion};
class testMainTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->bot->setUser([ 'name' => 'barbot'])->receives('desc')->assertReply('No entiendo que quieres decir, vuelve a intentarlo.');
    }
    public function testAdminTest(){ 
     $this->bot->setUser([ 'name' => 'barbot', 'email' => 'barbot5@gmail.com'
            ])->receives('admin')
            ->assertQuestion('Hola admin, las opciones disponibles son: ');
    }
    // 
    public function testAcercaTest(){
        $msj = "Este bot de charla fue desarrollado por:".
            "Jorge Andres llanos <jorgea.llanoc@autonoma.edu.co>".
            "Fabian Lopez <@autonoma.edu.co>".
            "Islen <@autonoma.edu.co>".
            "Desarrollado para metodos agiles del desarollo del software".
            "de la Maestria de gestion y desarollo de proyectos de software.";
        $this->bot->setUser([ 'name' => 'barbot'])->receives('acerca')
            ->assertReplies($msj);
    }
    public function testRockTest(){
        $genre = Genero::where('nombre','Rock')->first();
        foreach ($genre->canciones as $key => $value) {
            $valor[$key]=($key+1).$value->infoSong();
        }
        $this->bot->setUser(['name'=>'barbot','email'=>'barbot5@gmail.com'])
                  ->receives('genero Rock')
                  ->assertReplies($valor);
    }
      public function testBaladaTest(){
        $genre = Genero::where('nombre','Balada')->first();
        foreach ($genre->canciones as $key => $value) {
            $valor[$key]=($key+1).$value->infoSong();
        }
        $this->bot->setUser(['name'=>'barbot','email'=>'barbot5@gmail.com'])
                  ->receives('genero Balada')
                  ->assertReplies($valor);
    }
     public function testSalsaTest(){
        $genre = Genero::where('nombre','Salsa')->first();
        foreach ($genre->canciones as $key => $value) {
            $valor[$key]=($key+1).$value->infoSong();
        }
        $this->bot->setUser(['name'=>'barbot','email'=>'barbot5@gmail.com'])
                  ->receives('genero Salsa')
                  ->assertReplies($valor);
    }
     public function testNoGeneroTest(){
        $this->bot->setUser(['name'=>'barbot','email'=>'barbot5@gmail.com'])
                  ->receives('genero sssssss')
                  ->assertReply('genero no encontrado');
    }
    public function testListarTest(){
        $canciones=Cancion::all();
        foreach ($canciones as $key => $value) {
            $valor[$key]=($key+1).$value->infoSong();
        }
        $this->bot->setUser(['name'=>'barbot','email'=>'barbot5@gmail.com'])
                  ->receives('listar')
                  ->assertReplies($valor);
    }
}