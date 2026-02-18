<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} — {{ $funnel->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; padding: 24px; background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { max-width: 560px; width: 100%; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 32px; }
        .content { margin-bottom: 24px; line-height: 1.6; }
        .content img { max-width: 100%; height: auto; }
        form label { display: block; margin-bottom: 6px; font-weight: 600; }
        form input[type="text"], form input[type="email"], form input[type="tel"] { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px; }
        form button { background: #1e40af; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        form button:hover { background: #1e3a8a; }
        .message { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="margin-top: 0;">{{ $page->title }}</h1>

        @if(session('success'))
            <div class="message success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="message error">
                @foreach($errors->all() as $e) {{ $e }} @endforeach
            </div>
        @endif

        <div class="content">
            {!! $page->content ?? '' !!}
        </div>

        @if(in_array($page->type, ['opt-in', 'landing']))
            <form method="POST" action="{{ route('leads.capture') }}" id="capture-form">
                @csrf
                <input type="hidden" name="funnel_slug" value="{{ $funnel->slug }}">
                <input type="hidden" name="page_slug" value="{{ $page->slug }}">
                <div>
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}">
                </div>
                <div>
                    <label for="phone">Phone (optional)</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}">
                </div>
                <button type="submit">Submit</button>
            </form>
        @endif
    </div>
</body>
</html>
