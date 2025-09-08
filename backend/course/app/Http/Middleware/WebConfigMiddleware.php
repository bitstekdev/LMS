<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebConfigMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cache settings to avoid multiple DB hits
        $settings = [
            'system_title' => get_settings('system_title'),
            'timezone' => get_settings('timezone'),
            'protocol' => get_settings('protocol'),
            'smtp_host' => get_settings('smtp_host'),
            'smtp_port' => get_settings('smtp_port'),
            'smtp_crypto' => get_settings('smtp_crypto'),
            'smtp_user' => get_settings('smtp_user'),
            'smtp_pass' => get_settings('smtp_pass'),
            'smtp_from_email' => get_settings('smtp_from_email'),
        ];

        config([
            'app.name' => $settings['system_title'],
            'app.timezone' => $settings['timezone'],

            // SMTP configuration
            'mail.mailers.smtp.transport' => $settings['protocol'],
            'mail.mailers.smtp.host' => $settings['smtp_host'],
            'mail.mailers.smtp.port' => $settings['smtp_port'],
            'mail.mailers.smtp.encryption' => $settings['smtp_crypto'],
            'mail.mailers.smtp.username' => $settings['smtp_user'],
            'mail.mailers.smtp.password' => $settings['smtp_pass'],
            'mail.mailers.smtp.timeout' => null,
            'mail.mailers.smtp.local_domain' => $request->getHost(),
            'mail.from.name' => $settings['system_title'],
            'mail.from.address' => $settings['smtp_from_email'],
        ]);

        return $next($request);
    }
}
