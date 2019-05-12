<?php 

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\DB;
use App\{Cancion,Genero,Artista,Lista,Pivot};
use Auth;

class ClientConversation extends Conversation
{

    protected $canciones;
    protected $mi_lista;
    protected $key;

    function __construct()
    {
        $this->canciones = Cancion::orderBy('nombre','ASC')->get();
        $this->generos = Genero::orderBy('nombre','ASC')->get();
        if(\Cache::has(Auth::user()->clistas())){
            $this->mi_lista = \Cache::get(Auth::user()->clistas());
            $this->key = count($this->mi_lista)>0 ? ((int)collect($this->mi_lista)->keys()->last()+1):1;
        }else{
            $this->key = 1;
        }
    }
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->opciones();
    }

###########################################################################################################

    private function opciones(){
        $question = Question::create("Las opciones disponibles son: ")
            ->fallback('Error, valor no encontrado')
            ->addButtons([
                Button::create('Filtrar genero')->value('filter_genre'),
                Button::create('Ver Playlists')->value('ver_playlists'),
                Button::create('Ver Lista actual')->value('list_actual'),
                Button::create('Agregar cancion a la playlist')->value('add_song'),
                Button::create('Finalizar')->value('finalizar'),
                Button::create('Cancelar')->value('cancelar'),
            ]);
            
        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if($answer->getValue()=='filter_genre'){
                    $this->generos();
                }elseif($answer->getValue()=='add_song'){
                    $this->add_song('Canciones en el bar ',$this->canciones);
                }
                elseif($answer->getValue()=='list_actual'){
                    $this->eliminarCancion();
                }
                elseif($answer->getValue()=='ver_playlists'){
                    $this->playlists();
                }
                elseif($answer->getValue()=='cancelar'){
                    unset($this->mi_lista);
                    // \Cache::forget(Auth::user()->clistas());
                    $this->say('limpiaste la playlist en proceso');
                    $this->opciones();
                }
                elseif($answer->getValue()=='finalizar'){
                    $this->crear_playlist();
                    $this->say('creaste una playlist con las siguientes canciones');
                    $this->listsongs($this->mi_lista);
                    unset($this->mi_lista);
                    // \Cache::forget(Auth::user()->clistas());
                }
            }
        });
    }

###############################################################################################################
    private function yesno($msg){
        $question = Question::create($msg)
                     ->fallback('Error, valor no encontrado')
                     ->callbackId ('Come on!')
                     ->addButtons([ Button::create('Si')->value('si'), Button::create('No')->value('no'),]);
        return $question;
    }

###########################################################################################################
    public function add_song($msg , $canciones){
           return $this->ask($this->songsbtn($msg, $canciones), function (Answer $answer) use ($canciones){
                if ($answer->isInteractiveMessageReply()) {
                     $this->mi_lista[$this->key] = $this->canciones->find($answer->getText());
                     $this->ask($this->yesno("Hecho!! ✅, Agregar otra canción"),function (Answer $answer) use ($canciones) {
                            if($answer->getValue()=='si'){
                                $this->key++;
                                $this->add_song("Agregando cancion numero: ".$this->key." en la lista", $canciones->whereNotIn('id',collect($this->mi_lista)->pluck('id')));
                            }else{
                                \Cache::put(Auth::user()->clistas(),$this->mi_lista,12000);
                                $this->opciones();
                            }
                        });
                }else{
                    $this->say(Inspiring::quote());
                }
            });
    }
###########################################################################################################

    private function crear_playlist(){
        if($this->mi_lista!=null){
            $list=Lista::create(['user_id'=>Auth::id()]);
            foreach ($this->mi_lista as $value) {
                Pivot::create(['cancion_id'=>$value->id,'lista_id'=>$list->id]);
            }
        }else{
            $this->say('No hay lista para grabar');
        }
    }
###########################################################################################################

    private function playlists(){
        $lists = Auth::user()->listas;
        if($lists->count()<0){
            $this->say('No hay playlist');
        }else{
            $question = Question::create('Selecciona una Lista')
                     ->fallback('Error, cancion no encontrada')
                     ->callbackId ('Come on!');
            foreach ($lists as $key => $value) {
                 $question->addButtons([  Button::create('#'.($key+1).'\n Numero de canciones: '.$value->cant().' Estado de la lista: '.$value->estado)->value($value->id), ]);
            }
        
            $this->ask($question,function (Answer $answer) use ($lists){
                $lista = $lists->find($answer->getValue());
                $this->listsongs($lista->canciones);
                });
        }
    }

###########################################################################################################
 # genernos
    private function generos(){
        return $this->ask($this->btns("Los generos disponibles son, elige uno: ",$this->generos), function (Answer $answer){
              if ($answer->isInteractiveMessageReply()) {
                    $genero=$this->generos->find($answer->getValue());
                    $this->add_song('Canciones de '.$genero->nombre,$genero->canciones->whereNotIn('id',collect($this->mi_lista)->pluck('id')));
                 }else{
                    $this->say(Inspiring::quote());
                 }
        });
    }
#####################################################################################################
    #listar canciones en botones
    private function songsbtn($msg, $songs){
        $question = Question::create($msg)
                 ->fallback('Error, cancion no encontrada')
                 ->callbackId ('Come on!');
        foreach ($songs as $key => $value) {
             $question->addButtons([  Button::create('#'.$key.' '.ucwords($value->nombre))->value($value->id), ]);
        }
        return $question;
    }
#################################################################################################################
#generos en botones
    private function btns($msg,$array){
          $question = Question::create($msg)
                 ->fallback('Error, valor no encontrado')
                 ->callbackId ('Come on!');
            foreach ($array as $value) {
                 $question->addButtons([  Button::create($value->nombre)->value($value->id), ]);
            }
        return $question;
    }
#################################################################################################################
        public function listsongs($data){
            foreach ($data as $cancion) {
                $this->say($cancion->infoSong());
            }
        }
##################################################################################################################
        public function eliminarCancion(){
            if($this->mi_lista!=null){

                return $this->ask($this->songsbtn("Canciones en la actual playlist, selecciona cúal deseas quitar",$this->mi_lista),function(Answer $answer){
                    $pos = collect($this->mi_lista)->where('id',$answer->getValue())->keys()->first();
                    $this->ask($this->yesno("quieres quitar ".$pos."de la playlist en construcciòn ?"), function (Answer $asw) use ($pos){
                        if($asw->getValue()=='si'){                            
                            unset($this->mi_lista[$pos]);
                            \Cache::put(Auth::user()->clistas(),$this->mi_lista,12000);
                            $this->say('Hecho');
                        }else{
                            $this->say('Canceló el proceso');
                        }
                    });                    
                });

            }else{
                $this->say('No hay lista para mostrar');
            }
        }
###########################################################################################################
}
