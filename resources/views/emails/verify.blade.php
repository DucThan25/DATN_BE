<!DOCTYPE html>
<html>
<head>
    <title>Xác thực tài khoản HUCE NETWORK</title>
</head>
<body>
    <div class="container" 
            style="border: 1px solid beige ;
            background: beige ; 
            border-radius: 20px;
            display: flex;
            justify-content: center;
            align-items: center;">
        <div style="padding: 20px ; ">
            <h2>Xác thực địa chỉ Email của bạn</h2>
            <p>Cảm ơn bạn đã đăng ký tài khoản</p>
            <br>Tài khoản của bạn là: </p>
            <p>Email: {{$user->email}}</p>
            <p>Mật khẩu: {{\App\Models\User::PASSWORD_DEFAULT}}</p>
            <a href="{{ url('api/auth/verify-email/' . $user->token) }}">Xác thực tài khoản</a>
            <p>Nếu bạn không đăng ký trên trang web của chúng tôi, bạn có thể bỏ qua email này.</p>
        </div>
      </div>
</body>
</html>

