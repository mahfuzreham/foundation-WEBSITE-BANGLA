<?php
declare(strict_types=1);
$root=__DIR__; $configDir=$root.'/config'; $lockFile=$configDir.'/installed.lock';
if(file_exists($lockFile)){http_response_code(403);exit('Installation already completed.');}
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
$err='';$done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
 $host=trim($_POST['host']??'localhost');$db=trim($_POST['db']??'');$user=trim($_POST['user']??'');$pass=(string)($_POST['pass']??'');$au=trim($_POST['au']??'admin');$ap=(string)($_POST['ap']??'');
 try{
  if(!$db||!$user||strlen($ap)<8)throw new Exception('সব তথ্য দিন। Password কমপক্ষে ৮ অক্ষরের হতে হবে।');
  $pdo=new PDO("mysql:host=$host;charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $safe=str_replace('\\','',$db);$pdo->exec("CREATE DATABASE IF NOT EXISTS \`$safe\` CHARACTER SET utf8mb4");$pdo->exec("USE \`$safe\`");
  $tables=[
   "CREATE TABLE IF NOT EXISTS admins(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(60) UNIQUE,password VARCHAR(255),name VARCHAR(100),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
   "CREATE TABLE IF NOT EXISTS notices(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255),body TEXT,status TINYINT DEFAULT 1,published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
   "CREATE TABLE IF NOT EXISTS donations(id INT AUTO_INCREMENT PRIMARY KEY,donor_name VARCHAR(150),phone VARCHAR(30),amount DECIMAL(12,2),method VARCHAR(30),transaction_id VARCHAR(100),message TEXT,payment_status VARCHAR(20) DEFAULT 'Pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
   "CREATE TABLE IF NOT EXISTS members(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150),father_name VARCHAR(150),phone VARCHAR(30),email VARCHAR(150),address TEXT,occupation VARCHAR(150),status VARCHAR(20) DEFAULT 'Pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
   "CREATE TABLE IF NOT EXISTS gallery(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255),image VARCHAR(255),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
  ];foreach($tables as $q)$pdo->exec($q);
  $s=$pdo->prepare("SELECT id FROM admins WHERE username=?");$s->execute([$au]);if(!$s->fetch()){$s=$pdo->prepare("INSERT INTO admins(username,password,name) VALUES(?,?,?)");$s->execute([$au,password_hash($ap,PASSWORD_DEFAULT),'Administrator']);}
  if(!is_dir($configDir))mkdir($configDir,0755,true);
  $cfg="<?php\ndefine('DB_HOST',".var_export($host,true).");\ndefine('DB_NAME',".var_export($db,true).");\ndefine('DB_USER',".var_export($user,true).");\ndefine('DB_PASS',".var_export($pass,true).");\n\$pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);";
  if(file_put_contents($configDir.'/database.php',$cfg,LOCK_EX)===false)throw new Exception('Config write failed');
  if(!is_dir($root.'/uploads/gallery'))mkdir($root.'/uploads/gallery',0755,true);
  file_put_contents($lockFile,date('c'));$done=true;
 }catch(Throwable $e){$err=$e->getMessage();}
}
?><!doctype html><html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Foundation Installer</title><style>body{font-family:Arial;background:#f3f7f3}.box{max-width:650px;margin:40px auto;background:#fff;padding:30px;border-radius:12px}input{width:100%;padding:11px;margin:6px 0 12px;box-sizing:border-box}button{width:100%;padding:13px;background:#087a34;color:#fff;border:0;border-radius:6px}.err{color:#b22}.ok{color:#176b2c}</style><body><div class="box"><?php if($done):?><h2 class="ok">Installation সফল!</h2><p><a href="admin/login.php">Admin Login</a> | <a href="index.php">Website</a></p><b>Security: install.php delete করুন।</b><?php else:?><h1>🌿 Foundation Installer</h1><?php if($err):?><p class="err"><?=h($err)?></p><?php endif;?><form method="post">DB Host<input name="host" value="localhost">Database Name<input name="db" required>Database Username<input name="user" required>Database Password<input type="password" name="pass" required>Admin Username<input name="au" value="admin" required>Admin Password<input type="password" name="ap" minlength="8" required><button>Install Now</button></form><?php endif;?></div></body></html>