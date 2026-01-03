<!DOCTYPE html>
<html>
<head>
<title>Twitter / X Signup</title>
<style>
body{font-family:Arial;background:#f4f6fb}
.card{width:400px;margin:60px auto;background:#fff;padding:20px;border-radius:10px}
input,select,button{width:100%;padding:10px;margin:8px 0}
button{background:#111;color:#fff;border:0;border-radius:6px}
</style>
</head>
<body>
<div class="card">
<h2>Twitter / X</h2>
<form method="post" action="submit.php" onsubmit="return validate()">

<input name="display_name" placeholder="Display Name">
<input name="handle" placeholder="Handle">
<input name="email" placeholder="Email">
<input type="password" name="password" placeholder="Password">
<input type="date" name="dob">

<button>Sign Up</button>
</form>
</div>

<script>
function validate(){
  let inputs=document.querySelectorAll("input");
  for(let i of inputs){ if(i.value.trim()===""){ alert("All fields required"); return false; } }
  return true;
}
</script>
</body>
</html>