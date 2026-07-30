<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#8e879f; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#8e879f; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:560px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" height="48" style="height:48px; display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; border-radius:12px; box-shadow:0 1px 3px rgba(79,70,229,0.08), 0 12px 24px -8px rgba(79,70,229,0.15); overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height:6px; background:linear-gradient(90deg,#7c3aed,#4f46e5,#f43f5e); font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:32px;">
                                        <p style="margin:0 0 4px; font-size:12px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:#4f46e5;">
                                            Encuesta NPS
                                        </p>
                                        <h1 style="margin:0 0 16px; font-size:20px; color:#18181b;">
                                            Hola {{ $name }}.
                                        </h1>
                                        <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            {{ $question }}
                                        </p>

                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                            <tr>
                                                @foreach ($scoreLinks as $score => $url)
                                                    <td style="padding:2px;">
                                                        <a href="{{ $url }}" style="display:block; width:28px; height:28px; line-height:28px; text-align:center; border-radius:6px; background-color:{{ $score <= 6 ? '#fee2e2' : ($score <= 8 ? '#fef3c7' : '#d1fae5') }}; color:{{ $score <= 6 ? '#b91c1c' : ($score <= 8 ? '#92400e' : '#065f46') }}; font-size:13px; font-weight:600; text-decoration:none;">
                                                            {{ $score }}
                                                        </a>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">
                                            <tr>
                                                <td align="left" style="font-size:12px; color:#a1a1aa;">Nada probable</td>
                                                <td align="right" style="font-size:12px; color:#a1a1aa;">Muy probable</td>
                                            </tr>
                                        </table>

                                        <p style="margin:24px 0 0; font-size:13px; line-height:1.5; color:#a1a1aa;">
                                            Si los botones no funcionan, respondé desde este enlace: <a href="{{ $scoreLinks[10] }}" style="color:#4f46e5;">calificar con 10</a> (o reemplazá el número al final del enlace por tu puntaje).
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top:24px;">
                            <p style="margin:0; font-size:12px; color:#c7c7d9;">
                                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
