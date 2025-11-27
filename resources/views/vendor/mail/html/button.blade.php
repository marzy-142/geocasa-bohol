@php($color = $color ?? '#3182ce')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
<tr>
<td align="center">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <a href="{{ $url }}" target="_blank" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; background:linear-gradient(135deg,#2b6cb0,#3182ce); border-radius:10px; padding:14px 30px; color:#ffffff; display:inline-block; font-weight:600; font-size:15px; text-decoration:none; letter-spacing:0.3px;">
                {{ $slot }}
            </a>
        </td>
    </tr>
    </table>
</td>
</tr>
</table>
