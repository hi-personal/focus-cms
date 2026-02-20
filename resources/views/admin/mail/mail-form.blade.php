<x-app-layout>
    <div class="container">
        <h1>Email Küldése</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.send-mail') }}">
            @csrf

            <div class="form-group">
                <label for="mail_to">Címzett:</label>
                <input type="email" class="form-control" id="mail_to" name="mail_to" required>
            </div>

            <div class="form-group">
                <label for="mail_subject">Tárgy:</label>
                <input type="text" class="form-control" id="mail_subject" name="mail_subject" required>
            </div>

            <div class="form-group">
                <label for="mail_body">Üzenet:</label>
                <textarea class="form-control" id="mail_body" name="mail_body" rows="5" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Küldés</button>
        </form>
    </div>
</x-app-layout>