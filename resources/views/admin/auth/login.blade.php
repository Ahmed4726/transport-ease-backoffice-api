<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Login | Transport Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:'Poppins',sans-serif;

            background:linear-gradient(135deg,#0d6efd,#4f8ef7);

            min-height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;

        }

        .login-card{

            width:420px;

            background:#fff;

            border-radius:20px;

            padding:40px;

            box-shadow:0 15px 40px rgba(0,0,0,.2);

        }

        .logo{

            width:80px;

            height:80px;

            border-radius:50%;

            background:#0d6efd;

            color:#fff;

            display:flex;

            justify-content:center;

            align-items:center;

            margin:auto;

            font-size:35px;

        }

        h3{

            font-weight:700;

            margin-top:20px;

        }

        .form-control{

            height:50px;

            border-radius:10px;

        }

        .btn-login{

            height:50px;

            border-radius:10px;

            font-weight:600;

        }

    </style>

</head>

<body>

<div class="login-card">

    <div class="text-center">

        <div class="logo">

            <i class="bi bi-bus-front-fill"></i>

        </div>

        <h3>Transport Admin</h3>

        <p class="text-muted">

            Login to continue

        </p>

    </div>

    @if($errors->any())

        <div class="alert alert-danger">

            {{ $errors->first() }}

        </div>

    @endif

    <form action="{{ route('admin.authenticate') }}" method="POST">

        @csrf

        <div class="mb-3">

            <label class="form-label">

                Email

            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
            >

        </div>

        <div class="mb-3">

            <label class="form-label">

                Password

            </label>

            <input
                type="password"
                name="password"
                class="form-control"
                required
            >

        </div>

        <div class="form-check mb-4">

            <input
                type="checkbox"
                class="form-check-input"
                name="remember"
                id="remember">

            <label
                class="form-check-label"
                for="remember">

                Remember Me

            </label>

        </div>

        <button
            class="btn btn-primary btn-login w-100">

            <i class="bi bi-box-arrow-in-right"></i>

            Login

        </button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
