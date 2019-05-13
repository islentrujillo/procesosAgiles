<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\{ExampleConversation,AdminConversacion,ClientConversation};
use Auth;

class BotManController extends Controller
{

    // function __construct()
    // {
    //     $this->middleware('auth');
    // }
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {
        $bot->startConversation(new ExampleConversation());
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function administrar(BotMan $bot)
    {
        // $this->middleware('admin');
        if(Auth::user()->email=='barbot@gmail.com'){
            $bot->startConversation(new AdminConversacion());
        }else{
            $bot->say('no entiendo lo que quiere decir');
        }
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function cliente(BotMan $bot)
    {
        // $this->middleware('cliente');
        $bot->startConversation(new ClientConversation());
    }
}
