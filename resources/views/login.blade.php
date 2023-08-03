<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <style>
        .grecaptcha-badge { visibility: hidden; }
    </style>
</head>
<body>
    <div class="row justify-content-center mt-5">
        <div class="col-lg-4">
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
                    <form action="{{ route('login') }}" id="login-form" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="username" name="username" class="form-control" id="username" placeholder="Masukkan Username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                        </div>
                        <br>
                        <div class="mb-3">
                            <div class="d-grid">
                                <input type="hidden" name="g-recaptcha-response" id="hidden-input"/>
                                <button class="btn btn-primary g-recaptcha" 
                                data-sitekey="{{config('services.recaptcha.site_key')}}" 
                                data-callback='onSubmit' 
                                data-action='login'>Submit</button>
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
<script>
   function onSubmit(token) {
     document.getElementById('hidden-input').value = token; 
     document.getElementById("login-form").submit();
   }
  </script>
</html>