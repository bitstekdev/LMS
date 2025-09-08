<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends Controller
{
    public function download_certificate($identifier)
    {
        $certificate = Certificate::where('identifier', $identifier)->first();

        if (! $certificate) {
            return redirect()->route('home')->with('error', get_phrase('Certificate not found at this URL.'));
        }

        $qr_code_content_value = route('certificate', ['identifier' => $identifier]);
        $qrcode = QrCode::size(300)->generate($qr_code_content_value);

        return view('curriculum.certificate.download', [
            'certificate' => $certificate,
            'qrcode' => $qrcode,
        ]);
    }
}
