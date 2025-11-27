@component('vendor.mail.html.layout')
{{-- Body --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#2d3748;font-size:16px;line-height:1.6;">
    <tr>
        <td style="padding:0;">
            {{ Illuminate\Mail\Markdown::parse($slot) }}
        </td>
    </tr>
</table>

{{-- Subcopy --}}
@isset($subcopy)
    @slot('subcopy')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding:12px 0 0; color:#718096; font-size:12px;">
                    {{ Illuminate\Mail\Markdown::parse($subcopy) }}
                </td>
            </tr>
        </table>
    @endslot
@endisset
@endcomponent
