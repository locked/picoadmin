<?php

namespace App\Http\Controllers;

use App\Models\Firmware;
use Illuminate\Http\Response;

class FirmwareController extends Controller
{
    public function download(Firmware $firmware)
    {
        return response($firmware->data, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"firmware-{$firmware->deviceModel->type}-{$firmware->version}.bin\"",
        ]);
    }
}
