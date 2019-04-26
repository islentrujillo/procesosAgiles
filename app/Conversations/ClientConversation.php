<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\DB;
use App\{Cancion,Genero,Artista};

class ClientConversation extends Conversation
{

	protected $canciones;
	protected $mi_lista;
	protected $agregar;
	protected $key;

	function __construct()
	{
		$this->canciones = Cancion::orderBy('nombre','ASC')->get();
		$this->agregar = true;
		$this->key = 1;
	}
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->cliente();
    }

	public function add_song(){
		$this->ask('Escoger cancion, escribe su codigo ',function(Answer $answer) {
            $valor=DB::table('cancions')->find($answer->getText());
            if($valor==null){ $this->cliente(); }
            $this->mi_lista[$this->key] = $valor;
            $this->say('Elegiste: '.$answer->getText());
            $this->ask('Agregar otra cancion ? Yes/No ? Say YES or NO', [
            [   'pattern' => 'yes|yep|YES|y|Y|Yes|si|Si|SI|s',
                'callback' => function(){
                    $this->key++;
                    $this->add_song();
                }],[
                'pattern' => 'nah|no|nope|NO|n',
                'callback' => function () {
                    $this->lista();
                    $this->cliente();
                }]
            ]);
        });
	}

    public function cliente(){
    	$this->say('Hola Bienvenido: ');
        $this->opciones();
    }

    private function opciones(){
    	$question = Question::create("Hola Cliente, las opciones disponibles son: ")
            ->fallback('Error, valor no encontrado')
            ->addButtons([
                Button::create('Filtrar genero')->value('filter_genre'),
                Button::create('Ver Playlists')->value('ver_playlists'),
                Button::create('Agregar cancion a la playlist')->value('add_song'),
                Button::create('Quitar cancion de la playlist')->value('rem_song'),
                Button::create('Finalizar')->value('finalizar'),
                Button::create('Cancelar')->value('cancelar'),
            ]);
            
        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if($answer->getValue()=='filter_genre'){
                    $this->filtrar();
                }elseif($answer->getValue()=='add_song'){
                    $this->add_song();
                }
                elseif($answer->getValue()=='ver_playlists'){
                    $this->say('playlists');
                }
                elseif($answer->getValue()=='cancelar'){
                    unset($this->mi_lista);
                    $this->say('limpiaste la playlist en proceso');
                    $this->cliente();
                }
                elseif($answer->getValue()=='finalizar'){
                    $this->say('creaste una playlist');
                }
                elseif($answer->getValue()=='rem_song'){
                    $this->lista();
                    $this->ask('codigo a quitar', function (Answer $answer){
                        $this->say($answer->getText());
                    });
                }
            }
        });
    }

    private function filtrar(){
        $this->ask('Ingrese genero: ', function (Answer $answer){
            $genre = DB::table('generos')->where('nombre',ucwords($answer->getText()))->first();
            $valores=DB::table('cancions')->where('genero_id',$genre->id)->get();
            foreach ($valores as $cancion) {
                $this->say('titulo :'.$cancion->nombre."\n duracion: ".$cancion->duracion." Codigo: [code] ".$cancion->id);
            }
            $this->cliente();
        });
    }

    public function lista(){
        foreach($this->mi_lista as $lista){
            $this->say("Titulo: ".$lista->nombre." Codigo: ".$lista->id);
        }
    }
}
