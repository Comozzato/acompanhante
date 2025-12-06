<?php

namespace App\Observers;

use App\Models\Midia;
use Illuminate\Support\Facades\Storage;

class MidiaObserver
{
    //
    public function deleted(Midia $midia)
    {
        $midia->deleteFile();
    }
}
