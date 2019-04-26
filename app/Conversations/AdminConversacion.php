<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\DB;
use App\{Cancion,Genero,Artista};

class AdminConversacion extends Conversation
{

	protected $titulo;
	protected $genero;
	protected $artista;
	protected $duracion;

    protected $idCancion;
   /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->admin();
    }

    public function admin(){
		$question = Question::create("Hola admin, las opciones disponibles son: ")
            ->fallback('Error, valor no encontrado')
            // ->callbackId('admin')
            ->addButtons([
                Button::create('Listar canciones')->value('list'),
                Button::create('Agregar Genero')->value('add_genre'),
                Button::create('Remover Genero')->value('remove_genre'),
                Button::create('Agregar Artista')->value('add_artista'),
                Button::create('Agregar Cancion')->value('add'),
                Button::create('Remover Cancion')->value('remove'),
                Button::create('Editar Cancion')->value('edit'),
            ]);
            
        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'list') {
                    $canciones = Cancion::all();
                    foreach ($canciones as $key => $cancion) {
                    	$this->say(($key+1).$cancion->infoSong().' Codigo: #'.$cancion->id);
                    }
                }
                elseif($answer->getValue() === 'add_genre') {
                	$this->add_genre();
                }
                elseif($answer->getValue()==='remove_genre'){
                    $this->ask('Codigo del genero ?', function (Answer $response) {
                        $genre=DB::table('generos')->find($response->getText());
                        if($genre==null){ $this->say('Genero no encontrado');
                        }else{
                            $this->say('Genero: '.$genre->nombre."\n");
                	        $this->eliminar_genre($genre);
                        }
                    });
                }
                elseif($answer->getValue() === 'add_artista') {
                	$this->add_artista();
                }
                elseif($answer->getValue() === 'add') {
                	$this->add_songs();
                }
                elseif($answer->getValue() === 'remove') {
        			$this->ask('Codigo de la canción ?', function (Answer $response) {
        				$song=DB::table('cancions')->find($response->getText());
        				if($song==null){ $this->say('Cancion no encontrada');
        				}else{
        					$this->say('Cancion: '.$song->nombre."\n".'Duración: '.$song->duracion." min");
        					$this->eliminar_song($song);
        				}
        			});
                }
                elseif($answer->getValue()==='edit'){
                    $this->ask('Codigo de la canción ?', function (Answer $response) {
                    $cancion = DB::table('cancions')->where('id',$response->getText())->first();
                    if($cancion == null){ $this->say('cancion  no encontrada');}
                    else{
                        $this->titulo = $cancion->nombre;
                        $this->duracion = $cancion->duracion;
                        $this->idCancion = $cancion->id;
                        $this->actualizar();
                    }
                });
                }
            }
        });
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////
/// añadir canciones
    public function add_songs(){
    	$this->say('Escogiste Agregar canción');
        $this->ask('Titulo de la canción ?', function (Answer $response) {
        	$this->titulo = $response->getText();
	        $this->say('titulo '.$response->getText());
			$this->say('Generos registrados: '.$this->genreActual());
		    $this->ask('Genero ', function (Answer $response) {
        		$this->genero = DB::table('generos')->where('nombre',ucwords($response->getText()))->first();
        		if($this->genero==null){
        			$this->say('este genero no existe: '.$response->getText());
        			// return false;
                    $this->admin();
              
        		}else{
		        	$this->say('genero '.$response->getText());
		        }
				$this->say('Artistas registrados: '.$this->artistasActual());
			    $this->ask('Artista ', function (Answer $response) {
			    	$this->artista = DB::table('artistas')->where('nombre',ucwords($response->getText()))->first();
		    		if($this->artista==null){
		    			$this->say('este artista no existe: '.$response->getText());
		    			// return false;
                        $this->admin();
		    		}else{
			        	$this->say('artista '.$response->getText());
					}
		    		$this->ask('Duración: ', function (Answer $response) {
						$this->duracion = $response->getText();
			        	$this->say('duracion '.$response->getText());
			        	$this->option_song(['nombre'=>$this->titulo,
			        						'artista_id'=>$this->artista->id,
			        						'genero_id'=>$this->genero->id,
			        						'duracion'=>$this->duracion]);
		    		});
				});
	    	});
	    });
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////
/// añadir genero
	public function add_genre(){
		$this->say('Generos registrados: '.$this->genreActual());
        $this->ask('Ingrese genero', function (Answer $response) {
        	$valor = DB::table('generos')->where('nombre',ucwords($response->getText()))->first();
        	if($valor!=null){
        		$this->say('este genero ya existe: '.$response->getText());
                    $this->admin();

        	}else{
        		$this->say('Genero ingresado: '.$response->getText());
        		$this->options($response->getText());
        	}
        });
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////
/// ////añadir artista
	public function add_artista(){
		$this->say('Artistas registrados: '.$this->artistasActual());
        $this->ask('Ingrese artista', function (Answer $response) {
        	$valor = DB::table('artistas')->where('nombre',ucwords($response->getText()))->first();
        	if($valor!=null){
        		$this->say('este artista ya existe: '.$response->getText());
                    $this->admin();
        	}else{        		
        		$this->say('Artista ingresado: '.$response->getText());
        		$this->option_artista($response->getText());
        	}
        });
	}

    public function genreActual(){
    	return Genero::all()->pluck('nombre');
    }

    public function artistasActual(){
    	return Artista::all()->pluck('nombre');
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion agregar genero
    public function options($valor){
		$this->ask('Guardar configuracion ? Say YES or NO', [
        	[   'pattern' => 'yes|yep|YES|y',
	            'callback' => function () use ($valor) {
	            	DB::table('generos')->insert(['nombre'=>ucwords($valor)]);
	        		$this->say('Genero '.$valor.' fue creado');
	        		$this->say('Estas en la raiz, lobby');
	            }],[
	            'pattern' => 'nah|no|nope|NO|n',
	            'callback' => function () {
	                $this->say('has cancelado el proceso');
                    $this->admin();

	            }]
    	]);
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion agregar artista
    public function option_artista($valor){
		$this->ask('Guardar configuracion ? Say YES or NO', [
        	[   'pattern' => 'yes|yep|YES|y',
	            'callback' => function () use ($valor) {
	            	DB::table('artistas')->insert(['nombre'=>ucwords($valor)]);
	        		$this->say('Artista '.$valor.' fue creado');
	        		$this->say('Estas en la raiz, lobby');
	            }],[
	            'pattern' => 'nah|no|nope|NO|n',
	            'callback' => function () {
	                $this->say('has cancelado el proceso');
                    $this->admin();

	            }]
    	]);
    }
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion agregar canción
    public function option_song(array $valor){
		$this->ask('Agregar cancion ? Say YES or NO', [
        	[   'pattern' => 'yes|yep|YES|y',
	            'callback' => function () use ($valor) {
	            	DB::table('cancions')->insert($valor);
	        		$this->say('Cancion creada '.$valor['nombre'].' fue creado');
	        		$this->say('Estas en la raiz, lobby');
	            }],[
	            'pattern' => 'nah|no|nope|NO|n',
	            'callback' => function () {
	                $this->say('has cancelado el proceso');
                    $this->admin();

	            }]
    	]);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion eliminar canción
    public function eliminar_song($valor){
    	$var = $valor->id;
		$this->ask('Eliminar cancion '.$valor->nombre.' ? Say YES or NO', [
        	[   'pattern' => 'yes|yep|YES|y',
	            'callback' => function () use ($var) {
	            	DB::table('cancions')->where('id',$var)->delete();
	        		$this->say('la Cancion fue eliminada');
	            }],[
	            'pattern' => 'nah|no|nope|NO|n',
	            'callback' => function () {
	                $this->say('has cancelado el proceso');
                    $this->admin();

	            }]
    	]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    public function eliminar_genre($valor){
        $var = $valor->id;
        $this->ask('Eliminar genero '.$valor->nombre.' ? Say YES or NO', [
            [   'pattern' => 'yes|yep|YES|y',
                'callback' => function () use ($var) {
                    DB::table('cancions')->where('genero_id',$var)->delete();
                    DB::table('generos')->where('id',$var)->delete();
                    $this->say('el genero fue eliminado');
                }],[
                'pattern' => 'nah|no|nope|NO|n',
                'callback' => function () {
                    $this->say('has cancelado el proceso');
                    $this->admin();

                }]
        ]);
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
///
///
    public function actualizar(){
        $this->ask('titulo actual '.$this->titulo.' si desea consevar este campo dejelo en blanco', function (Answer $answer) {
            if($answer->getText()===''){
                $this->say('conservado');
            }else{
                $this->titulo = $answer->getText();
            }
            $this->ask('Duracion actual '.$this->duracion.' minutos, si desea consevar este campo dejelo en blanco', function (Answer $answer){
                 if($answer->getText()===''){
                    $this->say('conservado');
                }else{
                    $this->duracion = $answer->getText();
                }
              $this->ask('Actualizar o no Say YES or NO debe ser igual', function (Answer $answer){
                if ($answer->getText()==='YES') {
                   DB::table('cancions')->where('id', $this->idCancion)->update(['nombre' => $this->titulo,'duracion'=>$this->duracion]);
                    $this->say('hecho');

                }else{
                    $this->say('cancelado');
                    $this->admin();
                }
              });

            });
        });
    }
}
