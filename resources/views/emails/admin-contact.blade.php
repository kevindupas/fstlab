@component('mail::message')

    <div style="margin-bottom: 20px;">
        <strong style="color: #666;">{{ $notifiable->local === 'fr' ? 'De' : 'From' }}:</strong>
        <div style="margin: 5px 0 0 0;">{{ $sender->name }}</div>
        <div style="color: #666;">{{ $sender->email }}</div>
    </div>

    <div style="margin-bottom: 20px;">
        <strong style="color: #666;">{{ $notifiable->local === 'fr' ? 'Sujet' : 'Subject' }}:</strong>
        <div style="margin: 5px 0 0 0;">{{ $subject }}</div>
    </div>

    @component('mail::panel')
        {{ $message }}
    @endcomponent

    @component('mail::subcopy')
        TLC-Labx
    @endcomponent

@endcomponent
