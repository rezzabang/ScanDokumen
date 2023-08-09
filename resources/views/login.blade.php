<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
</head>
<body>
    <div class="row justify-content-center mt-5">
        <div class="col-10 col-md-6 col-sm-8">
            <div class="card @if(Session::has('error')) shake-card @endif">
                <div class="card-header text-center">
                    <h1 class="card-title">Login</h1>
                </div>
                <div class="card-body">
                    @if(Session::has('error'))
                        <div class="alert alert-danger text-center" role="alert">
                            {{ Session::get('error') }}
                        </div>
                    @endif
                    <form action="{{ route('login') }}" method="POST" id="loginForm">
                        @csrf
                        <div class="mb-3 form-group">
                            <input type="hidden" class="g-recaptcha form-control" name="recaptcha" id="recaptcha_token">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="username" name="username" class="form-control" id="username" placeholder="Masukkan Username" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="captcha">Captcha</label>
                            <div class="captcha m-3">
                                <span>{!! captcha_img('flat') !!}</span>
                                <button type="button" class="btn btn-danger" class="reload" id="reload">
                                    &#x21bb;
                                </button>
                            </div>
                            <input id="captcha" type="text" class="form-control" placeholder="Enter Captcha" name="captcha">
                            @error('captcha')
                                <label for="" class="text-danger m-1">{{ $message }}</label>
                            @enderror
                        </div>
                        <br>
                        <div class="mb-3">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<br>
<footer>
    <div class="container">
        <div class="text-center">
            <p>&copy; 2023 Created with <span class="love">&#10084;</span></p>
        </div>
    </div>
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    $('#reload').click(function () {
        $.ajax({
            type: 'GET',
            url: '{{ route('reloadCaptcha') }}',
            success: function (data) {
                $(".captcha span").html(data.captcha);
            }
        });
    });

    grecaptcha.ready(function () {
                document.getElementById('loginForm').addEventListener("submit", function (event) {
                    event.preventDefault();
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'submit' })
                        .then(function (token) {
                            document.getElementById("recaptcha_token").value = token;
                            document.getElementById('loginForm').submit();
                        });
                });
            });
</script>
</html>
