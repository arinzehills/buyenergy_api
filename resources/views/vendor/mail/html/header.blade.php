<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
{{-- @elseif (trim($slot) === 'BuyEnergy')
<img src="{{asset('images/logo_transparent.png')}}" class="logo" alt="BuyEnergy"> --}}
@else
<div class="mySlot">{{ $slot }}</div>
@endif
</a>
</td>
</tr>
