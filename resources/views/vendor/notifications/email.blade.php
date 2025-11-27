@php($level = $level ?? 'primary')
@component('vendor.mail.html.layout')
@slot('title') {{ $greeting ?? 'GeoCasa Bohol' }} @endslot

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#2d3748;font-size:16px;line-height:1.6;">
    @if (! empty($greeting))
        <tr>
            <td style="font-size:22px; font-weight:800; letter-spacing:-.4px; color:#1f2937; padding:0 0 8px;">{{ $greeting }}</td>
        </tr>
    @endif

    @foreach ($introLines as $line)
        <tr>
            <td style="padding: 8px 0; color:#374151;">{{ $line }}</td>
        </tr>
    @endforeach

    @isset($actionText)
        @php
            switch ($level) {
                case 'success':
                    $color = '#22c55e';
                    break;
                case 'error':
                    $color = '#ef4444';
                    break;
                default:
                    $color = '#3182ce';
            }
        @endphp
        <tr>
            <td style="padding: 20px 0;">
                @component('vendor.mail.html.button', ['url' => $actionUrl, 'color' => $color])
                    {{ $actionText }}
                @endcomponent
            </td>
        </tr>
    @endisset

    @foreach ($outroLines as $line)
        <tr>
            <td style="padding: 8px 0; color:#374151;">{{ $line }}</td>
        </tr>
    @endforeach

    @if (! empty($salutation))
        <tr>
            <td style="padding-top:12px;">{!! $salutation !!}</td>
        </tr>
    @else
        <tr>
            <td style="padding-top:12px;">Regards,<br>GeoCasa Bohol</td>
        </tr>
    @endif
</table>

@isset($actionText)
    @slot('subcopy')
        If you’re having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser:
        <span class="break-all" style="word-break: break-all;">{{ $actionUrl }}</span>
    @endslot
@endisset
@endcomponent
