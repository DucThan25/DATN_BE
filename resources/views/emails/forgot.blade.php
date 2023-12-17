<!DOCTYPE html>
<html>
<head>
    <title> HUCE NETWORK</title>
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
            <h2>Lấy lại mật khẩu</h2>
            <p>Mật khẩu của {{$user->email}} đã được đổi thành: {{\App\Models\User::PASSWORD_DEFAULT}}</p>
        </div>
      </div>
</body>
</html>

