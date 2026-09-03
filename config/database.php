<?php
$dbDir=__DIR__.'/../database'; if(!is_dir($dbDir)) mkdir($dbDir,0755,true); $pdo=new PDO('sqlite:'.$dbDir.'/foundation.sqlite'); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS admins(id INTEGER PRIMARY KEY AUTOINCREMENT,username TEXT UNIQUE,password TEXT NOT NULL,name TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS notices(id INTEGER PRIMARY KEY AUTOINCREMENT,title TEXT NOT NULL,body TEXT,published_at TEXT DEFAULT CURRENT_TIMESTAMP,status INTEGER DEFAULT 1)");
$pdo->exec("CREATE TABLE IF NOT EXISTS donations(id INTEGER PRIMARY KEY AUTOINCREMENT,donor_name TEXT NOT NULL,phone TEXT,amount REAL NOT NULL,method TEXT,transaction_id TEXT,message TEXT,payment_status TEXT DEFAULT 'Pending',created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS members(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,father_name TEXT,phone TEXT NOT NULL,email TEXT,address TEXT,occupation TEXT,status TEXT DEFAULT 'Pending',created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS gallery(id INTEGER PRIMARY KEY AUTOINCREMENT,title TEXT,image TEXT NOT NULL,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
if(!$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn()){ $s=$pdo->prepare('INSERT INTO admins(username,password,name) VALUES(?,?,?)'); $s->execute(['admin',password_hash('admin123',PASSWORD_DEFAULT),'Foundation Administrator']); }
