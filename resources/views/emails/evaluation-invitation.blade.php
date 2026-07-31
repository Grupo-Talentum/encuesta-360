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
                                    <td style="height:6px; background-color:#159895; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:32px;">
                                        <h1 style="margin:0 0 16px; font-size:20px; color:#18181b;">
                                            Hola {{ $evaluator->name }},
                                        </h1>
                                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            Hay una nueva oportunidad para compartir tu experiencia a través de Talentum Voice.
                                        </p>
                                        <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            En esta ocasión, nos gustaría conocer tu experiencia de colaboración con <strong>{{ $evaluatees->implode(', ') }}</strong>.
                                        </p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="border-radius:8px; background-color:#159895;">
                                                    <a href="{{ $url }}" style="display:inline-block; padding:12px 28px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none;">
                                                        Compartir mi experiencia
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin:24px 0 16px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            Tu punto de vista nos ayuda a reconocer fortalezas, identificar oportunidades y seguir construyendo una mejor experiencia para todos.
                                        </p>
                                        <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#3f3f46;">
                                            Gracias por formar parte de esta cultura de escucha y mejora continua.
                                        </p>
                                        <p style="margin:0 0 4px; font-size:14px; font-weight:600; color:#159895;">
                                            Talentum Voice
                                        </p>
                                        <p style="margin:0 0 24px; font-size:12px; font-style:italic; color:#a1a1aa;">
                                            Listening. Learning. Improving.
                                        </p>
                                        <p style="margin:0; font-size:13px; line-height:1.5; color:#a1a1aa;">
                                            ¿No puedes utilizar los botones? Puedes compartir tu experiencia desde este enlace: <a href="{{ $url }}" style="color:#159895;">Visualizar en el navegador</a>.
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
