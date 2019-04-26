<?php

use Illuminate\Database\Seeder;
use App\{Genero,Artista,Cancion,User};

class MusicSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['Salsa','Rock','Balada'] as $value) {
        	Genero::create(['nombre'=>$value]);
    	}
    	Artista::create(['nombre'=>'Niche']);
        Artista::create(['nombre'=>'Soda Stereo']);
        Artista::create(['nombre'=>'Luis Miguel']);
        $canciones = ['mi negra y la calentura','cuando pase el temblor 
','culpable o no','a tì Barranquilla','En la ciudad de la furia','ahora te puedes marchar','lamento guajiro','tratàme suavente','la incondicional','sueño','Lo que sangra','Repetida'];
        $duracion = ['6:12','4:20','5:20','5:00','5:52','3:50','6:58','4:15','4:45','5:27','5:10','4:35'];
        $x=1;
        foreach ($canciones as $key => $value) {
            Cancion::create(['nombre'=>$value,
                            'artista_id'=>$x,
                            'genero_id'=>$x,
                            'duracion'=> $duracion[$key]]);
            if($x==3) { $x=1;}else{$x++;}
        }
    	User::create(['name'=>'barbot', 'email'=>'barbot@gmail.com', 'password'=>bcrypt('secret')]);
    }
}
