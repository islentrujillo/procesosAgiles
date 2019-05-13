<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\DB;
use App\{Cancion,Genero,Artista,Lista};

class AdminConversacion extends Conversation
{

    protected $titulo;
    protected $genero;
    protected $artista;
    protected $duracion;

    protected $idCancion;

    protected $generos;
    protected $canciones;
    protected $noUpdate;

    function __construct()
    {
        $this->generos = Genero::orderBy('nombre','ASC')->get();
        $this->canciones = Cancion::orderBy('nombre','ASC')->get();
        $this->noUpdate=true;
    }
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
            ->callbackId('admin')
            ->addButtons([
                Button::create('Listar canciones')->value('list'),
                Button::create('Listar playlist')->value('playlist'),
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
                    $canciones = Cancion::orderBy('nombre','ASC')->get();
                    foreach ($canciones as $key => $cancion) {
                        $this->say(($key+1).$cancion->infoSong());
                    }
                    $this->admin();
                }
                elseif($answer->getValue() === 'add_genre') {
                    $this->add_genre();                    
                }
                elseif($answer->getValue() === 'playlist') {
                    $this->playlist();
                }
                elseif($answer->getValue()==='remove_genre'){
                    $this->eliminar_genre();
                }
                elseif($answer->getValue() === 'add_artista') {
                    $this->add_artista();
                }
                elseif($answer->getValue() === 'add') {
                    $this->add_songs();
                }
                elseif($answer->getValue() === 'remove') {
                         $this->eliminar_song();
                }
                elseif($answer->getValue()==='edit'){
                    $this->actualizar();
                }
            }
        });
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////
   private function generos(){
        return $this->ask($this->btns("Los generos disponibles son, elige uno: ",$this->generos), function (Answer $answer){
              if ($answer->isInteractiveMessageReply()) {
                    $this->genero = $answer->getValue();
                    $this->say('genero: '.$this->generos->find($this->genero)->nombre);
                 }else{
                    $this->say(Inspiring::quote());
                 }
            $this->artistas();
        });
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////
    private function artistas(){
        $artistas = Artista::all();
        return $this->ask($this->btns("Los Artistas disponibles son, elige uno: ",$artistas), function (Answer $answer) use ($artistas) {
              if ($answer->isInteractiveMessageReply()) {
                    $this->artista = $answer->getValue();
                    $this->say('Artista: '.$artistas[$this->artista-1]->nombre);
                 }else{
                    $this->say(Inspiring::quote());
                 }
                 if($this->noUpdate){
                    $this->duracion();
                 }else{
                    $this->opUpdate();
                 }
              });
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function duracion(){
        $this->ask('Duración: ', function (Answer $response) {
            $this->duracion = $response->getText();
            $this->say('duracion '.$response->getText());
            $this->boton_op(['nombre'=>$this->titulo,
                'artista_id'=>$this->artista,
                'genero_id'=>$this->genero,
                'duracion'=>$this->duracion]);
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
###############################################################################################################
    private function btns($msg,$array){
          $question = Question::create($msg)
                 ->fallback('Error, valor no encontrado')
                 ->callbackId ('Come on!');
            foreach ($array as $value) {
                 $question->addButtons([  Button::create($value->nombre)->value($value->id), ]);
            }
            // $question->addButton(Button::create('Cancelar')->value('cancelar'));
        return $question;
    }
################################################################################################################

////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function boton_op(array $data){
        return $this->ask($this->yesno("Paso final, Quieres agregar esta canción al sistema: "), function (Answer $answer) use ($data) {
            if ($answer->isInteractiveMessageReply()) {
                    $op = $answer->getValue()=='si';
            }
            $this->option_song($data,$op);
        });
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////
/// añadir canciones
    public function add_songs(){
        $this->say('Escogiste Agregar canción');
        $this->ask('Titulo de la canción ?', function (Answer $response) {
            $this->titulo = $response->getText();
            $this->say('Titulo '.$response->getText());
            $this->generos();
        });
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////
/// añadir genero
    public function add_genre(){
        $this->ask('Ingrese genero', function (Answer $response) {
            $valor = DB::table('generos')->where('nombre',ucwords($response->getText()))->first();
            if($valor!=null){
                $this->say('Este genero ya existe: '.$response->getText());
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
        $this->ask('Ingrese artista', function (Answer $response) {
            $valor = DB::table('artistas')->where('nombre',ucwords($response->getText()))->first();
            if($valor!=null){
                $this->say('Este artista ya existe: '.$response->getText());
            }else{              
                $this->say('Artista ingresado: '.$response->getText());
                $this->option_artista($response->getText());
            }
        });
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion agregar genero
    public function options($valor){                   
        return $this->ask($this->yesno("Paso final, Quieres agregar este genero al sistema: ".$valor." ?"), function (Answer $answer) use ($valor) {
            if ($answer->isInteractiveMessageReply()) {
                if($answer->getValue()=='si'){
                        DB::table('generos')->insert(['nombre'=>ucwords($valor)]);
                        $this->say('Genero '.$valor.' fue creado  ✅');
                }else{
                    $this->say('has cancelado el proceso');
                }
            }
            $this->admin();
        });
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion agregar artista
    public function option_artista($valor){
        return $this->ask($this->yesno("Paso final, Quieres agregar este Artista al sistema: ".$valor." ?"), function (Answer $answer) use ($valor) {
            if ($answer->isInteractiveMessageReply()) {
                if($answer->getValue()=='si'){
                    DB::table('artistas')->insert(['nombre'=>ucwords($valor)]);
                    $this->say('Artista '.$valor.' fue creado  ✅');
                }else{
                    $this->say('has cancelado el proceso');
                }
            }
            $this->admin();
        });
    }
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion agregar canción
    public function option_song(array $valor, $op){
        if($op){
            DB::table('cancions')->insert($valor);
            $this->say('Cancion creada '.$valor['nombre'].' fue creado ✅');
            $this->admin();
        }else{        
            $this->say('has cancelado el proceso');
            $this->admin();
        }
    }
    #####################################################################################################
    #listar canciones en botones
    private function songsbtn($msg){
        $question = Question::create($msg)
                 ->fallback('Error, cancion no encontrada')
                 ->callbackId ('Come on!');
        foreach ($this->canciones as $key => $value) {
             $question->addButtons([  Button::create('#'.$key.' '.ucwords($value->nombre))->value($value->id), ]);
        }
        return $question;
    }
    ######################################################################################################
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////7
/// opcion eliminar canción
    public function eliminar_song(){
        return $this->ask($this->songsbtn("Eliminar cancion de la lista"), function (Answer $answer) {
              if ($answer->isInteractiveMessageReply()) {
                    $this->op_del_song(collect($this->canciones)->firstWhere('id',$answer->getValue()));
                 }else{
                    $this->say(Inspiring::quote());
                 }
        });
    }
///////////////////////////// opciones para eliminar genero ///////////////////////////////////////////////////////
public function op_del_song($data){                 
    return $this->ask($this->yesno("Quieres eliminar ".$data['nombre']." cancion del sistema ?"), function (Answer $answer) use ($data){
        if ($answer->isInteractiveMessageReply()) {
            if($answer->getValue()=='si'){
                DB::table('cancions')->where('id',$data['id'])->delete();
                $this->say('la Canción '.$data['nombre'].' fue eliminada ✅');
            }else{
                $this->say('has cancelado el proceso');
            }
        }
    });
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function eliminar_genre(){
            return $this->ask($this->btns("Los generos existentes son, elige uno para eliminar: ",$this->generos), function (Answer $answer) {
              if ($answer->isInteractiveMessageReply()) {
                    $canciones=DB::table('cancions')->where('genero_id',$answer->getValue())->get();
                    if(count($canciones)>0){
                        $this->say('❌ NO PUEDES borrar este genero, porque tiene canciones');
                    }else{
                        $this->op_del_gen();
                    }
                 }else{
                    $this->say(Inspiring::quote());
                 }
            });
    }
///////////////////////////// opciones para eliminar genero ///////////////////////////////////////////////////////
public function op_del_gen(){
        return $this->ask($this->yesno("Paso final, Quieres eliminar este genero del sistema ?"), function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if($answer->getValue()=='si'){
                    DB::table('cancions')->where('genero_id',$this->genero)->delete();
                    DB::table('generos')->where('id',$this->genero)->delete();
                    $this->say('el genero fue eliminado ✅');
                }else{
                    $this->say('has cancelado el proceso');
                }
            }
        });
}

########################################################################################################################
public function playlist(){
    $list=Lista::all();
    $question = Question::create('Selecciona una Lista')
                 ->fallback('Error, cancion no encontrada')
                 ->callbackId ('Come on!');
    foreach ($list as $key => $value) {
         $question->addButtons([  Button::create('#'.($key+1).' Usuario: '.$value->user->name.'\n Numero de canciones: '.$value->cant().' Estado de la lista: '.$value->estado)->value($value->id), ]);
    }
    $this->ask($question,function (Answer $answer) use ($list){
        $lista = $list->find($answer->getValue());
        foreach ($lista->canciones as $key => $value) {
            $this->say($value->infoSong());
        }
        if($lista->estado =='Aprobada'){
            $this->ask($this->yesno("Reproducir Esta playlist ?"),function(Answer $answer) use ($lista){
                $lista->estado=$answer->getValue() ==  'si' ? 'Reproducida':'Aprobada';
                $lista->save();
                $this->say('Reproduciendo '.$lista->canciones->first()->infoSong().'!! ✅');
            });
        }else{
            $this->ask($this->yesno("Aprobar Esta playlist ?"),function(Answer $answer) use ($lista){
                    $lista->estado=$answer->getValue() ==  'si' ? 'Aprobada':'Rechazada' ;
                    $lista->save();
                    $this->say('Hecho!! ✅');
            });
        }
    });
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///
    public function actualizar(){
        return $this->ask($this->songsbtn("Editar cancion de la lista",$this->canciones), function (Answer $answer) {
              if ($answer->isInteractiveMessageReply()) {
                    $temp = collect($this->canciones)->firstWhere('id',$answer->getValue());
                    $this->idCancion=$temp->id;
                    $this->titulo = $temp->nombre;
                    $this->duracion = $temp->duracion;
                    $this->ask('titulo actual '.$temp->nombre.' si desea consevar este campo dejelo en blanco', function (Answer $answer) {
                        $this->titulo = $answer->getText()=='' ? $this->titulo : $answer->getText();
                        $this->noUpdate=false;
                        $this->ask('Duracion actual '.$this->duracion.' minutos, si desea consevar este campo dejelo en blanco', function (Answer $answer){
                            $this->duracion = $answer->getText() == '' ? $this->duracion : $answer->getText();
                            $this->generos();
                        });
                    });
                 }else{
                    $this->say(Inspiring::quote());
                 }
        });
    }
################################################################################################################
        private function opUpdate(){
            $this->ask($this->yesno("Actualizar o cancelar"), function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if($answer->getValue()=='si'){
                   DB::table('cancions')->where('id', $this->idCancion)
                   ->update(['nombre' => $this->titulo,'duracion'=>$this->duracion,'artista_id'=>$this->artista,'genero_id'=>$this->genero]);
                    $this->say('Hecho!! ✅');
                    $this->admin();
                }else{
                    $this->say('cancelado');
                    $this->admin();
                }
            }
            });
        }
################################################################################################################
}
