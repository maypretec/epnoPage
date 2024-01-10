<?php

namespace App\Console\Commands;

use Illuminate\Log\Events\MessageLogged;

class CustomizeLogMessage
{
    public function __invoke(MessageLogged $event)
    {
        $message = $event->formatter();
        $modifiedMessage = $this->customMessageModification($message);
        $event->formatter($modifiedMessage);
    }

    protected function customMessageModification($message)
    {
        $formatedMessage = explode("\n", $message);
        return($formatedMessage[0]);
    }
}
