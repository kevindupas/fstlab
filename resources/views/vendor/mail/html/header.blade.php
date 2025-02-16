<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === config('app.name'))
                <img src="{{ asset('logo/logo.png') }}" alt="{{ config('app.name') }}" style="height: 50px; width: auto;">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
