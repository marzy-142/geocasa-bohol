<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'GeoCasa Bohol' }}</title>
    <style>
        /* Email client resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        /* Apple link color fix */
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; }
        /* Android center fix */
        div[style*="margin: 16px 0;"] { margin: 0 !important; }
    </style>
</head>
<body style="background-color:#f6f9fc; margin:0; padding:24px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 6px 24px rgba(2,22,58,0.08);">
                    <tr>
                        <td>
                            @include('vendor.mail.html.header')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    @isset($subcopy)
                    <tr>
                        <td style="padding: 0 32px 8px;">
                            @include('vendor.mail.html.subcopy')
                        </td>
                    </tr>
                    @endisset
                    <tr>
                        <td>
                            @include('vendor.mail.html.footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
