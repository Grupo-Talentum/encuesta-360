<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:560px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <img src="{{ asset('images/talentum_voice.png') }}" alt="{{ config('app.name') }}" height="48" style="height:48px; display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #e4e4e7; border-radius:12px; box-shadow:0 1px 3px rgba(15,44,82,0.06); overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height:6px; background:linear-gradient(90deg,#159895 0%,#2dd4bf 70%,#0f2c52 100%); font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:32px;">
                                        <p style="margin:0 0 4px; font-size:12px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:#159895;">
                                            Evaluación 360
                                        </p>
                                        <h1 style="margin:0 0 16px; font-size:20px; color:#18181b;">
                                            Hola {{ $evaluator->name }}.
                                        </h1>
                                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            Tenés una nueva evaluación disponible en <strong>{{ $survey->title }}</strong>.
                                        </p>
                                        <p style="margin:0 0 8px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            Vas a evaluar a:
                                        </p>
                                        <p style="margin:0 0 24px; font-size:15px; font-weight:600; color:#18181b;">
                                            {{ $evaluatees->implode(', ') }}
                                        </p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="border-radius:8px; background-color:#159895;">
                                                    <a href="{{ $url }}" style="display:inline-block; padding:12px 28px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none;">
                                                        Responder encuesta
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin:24px 0 0; font-size:13px; line-height:1.5; color:#a1a1aa;">
                                            Si el botón no funciona, copiá y pegá este enlace en tu navegador:<br>
                                            <a href="{{ $url }}" style="color:#159895; word-break:break-all;">{{ $url }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top:24px;">
                            <p style="margin:0; font-size:12px; color:#a1a1aa;">
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
