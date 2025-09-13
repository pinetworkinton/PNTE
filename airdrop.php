 <?php
$host = 'sql107.infinityfree.com';
$db   = 'if0_37781627_1';
$user = 'if0_37781627';
$pass = 'Alihaji123';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) die("Database Connection Failed: " . $conn->connect_error);

session_start();

$ref = isset($_GET['ref']) ? $conn->real_escape_string($_GET['ref']) : null;

if (isset($_POST['register'])) {
    $wallet = trim($_POST['wallet']);
    $email  = trim($_POST['email']);
    $twitter = trim($_POST['twitter']);
    $youtube = trim($_POST['youtube']);

    if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $wallet)) {
        die("❌ Invalid EVM wallet address. It must start with 0x and have 40 hex characters.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("❌ Invalid email address.");
    }

if (!empty($twitter) && $twitter[0] !== '@') {
    die("❌ Twitter username must start with @");
}


if (!empty($youtube) && $youtube[0] !== '@') {
    die("❌ YouTube username must start with @");
}
    $check = $conn->query("SELECT * FROM airdrop_users WHERE wallet_address='$wallet' OR email='$email'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO airdrop_users (wallet_address,email,twitter,youtube,total_pnte,referrals) 
                      VALUES ('$wallet','$email','$twitter','$youtube',0,0)");

        if ($ref) {
            $conn->query("UPDATE airdrop_users 
                          SET referrals = referrals + 1, total_pnte = total_pnte + 500 
                          WHERE RIGHT(wallet_address,7) = '$ref'");
        }
        $_SESSION['wallet'] = $wallet;
    } else {
        $_SESSION['wallet'] = $wallet;
    }
}

$user = null;
if (isset($_SESSION['wallet'])) {
    $wallet = $_SESSION['wallet'];
    $res = $conn->query("SELECT * FROM airdrop_users WHERE wallet_address='$wallet'");
    $user = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title>PNTE Airdrop Referral</title>
<link rel="icon" href="assets/images/pinet.png" type="image/x-icon">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

 <style>
  html { scroll-behavior: smooth; }
  #preloader {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: #fff; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
  }
  .spinner {
    width: 60px; height: 60px;
    border: 6px solid #ddd;
    border-top: 6px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    margin: 0;
    background: linear-gradient(to right, #f8f9fa, #e0f7fa);
  }
  header {
    position: relative;
    height: 200px;
    background: url('assets/images/What-is-energy.jpg') no-repeat center center;
    background-size: cover;
  }
  header:hover {
  box-shadow: 0 0 25px rgba(0, 200, 150, 0.7);
}
  .header-nav {
    position: absolute;
    bottom: 65px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 30px;
    background: rgba(0,0,0,0.4);
    padding: 10px 20px;
    border-radius: 10px;
  }
  .header-nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 18px;
    padding: 8px 12px;
    border-radius: 6px;
    transition: background-color 0.3s;
  }
  .header-nav a:hover {
    box-shadow: 0 0 25px rgba(0, 200, 150, 0.7);
  }
  .container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 40px;
    padding: 40px;
  }


  .box {
    background: #fff;
    padding: 35px 30px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 600px;
    text-align: center;
    transition: all 0.3s ease;
  }
  .box:hover {
 
   box-shadow: 0 0 25px rgba(0, 200, 150, 0.7);
  }

  h1 { color: #333; }
  h3 { color: #555; }

  .input-group {
    display: flex;
    align-items: center;
    margin: 15px 0;
    border: 1px solid #ccc;
    border-radius: 10px;
    overflow: hidden;
    background: #f9f9f9;
}
.input-group span {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    color: #3498db;
    font-size: 18px;
    background: #e0f7fa;
}
.input-group input {
    flex: 1;
    padding: 12px 14px;
    border: none;
    font-size: 16px;
    outline: none;
    background: transparent;
}
  .input-group input:focus {
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52,152,219,0.4);
    outline: none;
  }
  .input-group::before {
    content: attr(data-icon);
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #3498db;
  }

  button {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    font-size: 18px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    background: linear-gradient(90deg,#3498db,#1abc9c);
    color: white;
    transition: all 0.3s ease;
  }
  button:hover {
    background: linear-gradient(90deg,#1abc9c,#16a085);
  }

  .ref-link-container {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 15px 0;
  }
  .ref-link-container input {
    flex: 1;
    font-family: monospace;
  }

  .footer-box {
    background: #f9f9f9;
    padding: 20px;
    margin-top: 40px;
    border-radius: 12px;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
    color: #555;
    font-size: 14px;
  }
</style>
</head>
<body>

<header>
  <nav class="header-nav">
    <a href="https://pinetworkinton.free.nf">MAIN</a>
  </nav>
</header>

<div class="container">
  <div class="box" id="airdrop" section>
    <h1>🚀 PNTE Airdrop</h1>

    <?php if (!$user): ?>
    <p>"Register your wallet and email to get your referral link "<li> Plus earn 1,000 PNTE for following us on Twitter and 700 PNTE for subscribing to our YouTube channel </li>
    
    <form method="post">
      <div class="input-group">
  
  <input type="text" name="wallet" placeholder="Wallet Address (EVM)" required>
</div>

<div class="input-group">
  
  <input type="email" name="email" placeholder="Email" required>
</div>

<div class="input-group">
  
  <input type="text" name="twitter" placeholder="Twitter Username">
</div>

<div class="input-group">
  
  <input type="text" name="youtube" placeholder="YouTube Username">
</div>
<p> register in the form below to claim your rewards! </p>
<div class="g-recaptcha" data-sitekey="6LejYMArAAAAAG5m2ZqoGDWj6sYLDFeS01JHdVUZ">
</div>
      <button type="submit" name="register">Register</button>
   
    </form>
    <?php else: ?>
    <h3>👛 Wallet: <?= htmlspecialchars($user['wallet_address']) ?></h3>
    <p>Total PNTE: <b><?= $user['total_pnte'] ?></b></p>
    <p>Successful Referrals: <?= $user['referrals'] ?></p>
    <p>🐦 Twitter: <?= htmlspecialchars($user['twitter']) ?></p>
    <p>📺 YouTube: <?= htmlspecialchars($user['youtube']) ?></p>

    <?php 
    $ref_link = substr($user['wallet_address'], -7);
    ?>
    <p>Share your referral link:</p>
<div class="ref-link-container" style="display:flex; flex-direction:column; gap:8px; max-width:100%;">
  
  <div style="display:flex; gap:8px; align-items:center; width:100%;">
    <input type="text" 
           value="https://pinetworkinton.free.nf/airdrop.php?ref=<?= htmlspecialchars($ref_link) ?>" 
           readonly 
           style="flex:1; padding:8px; font-size:14px; box-sizing:border-box; word-break:break-all;">
    
    <div class="share-buttons" style="display:flex; gap:4px;">
      <!-- Twitter -->
      <a href="https://twitter.com/intent/tweet?text=Check+this+airdrop!&url=https://pinetworkinton.free.nf/airdrop.php?ref=<?= htmlspecialchars($ref_link) ?>" target="_blank" 
         style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; background:#1DA1F2; color:white; border-radius:4px; font-weight:bold; text-decoration:none;">X</a>
      <!-- YouTube -->
      <a href="https://www.youtube.com/@pinet_pnte" target="_blank" 
         style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FF0000; color:white; border-radius:4px; font-weight:bold; text-decoration:none;">Y</a>
      <!-- Zealy -->
      <a href="https://zealy.io/cw/pinetpnte" target="_blank" 
         style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; background:#000000; color:white; border-radius:4px; font-weight:bold; text-decoration:none;">Z</a>
    </div>
  </div>

  
  <button onclick="this.previousElementSibling.querySelector('input').select();document.execCommand('copy');alert('Copied!');" 
          style="width:100%; padding:10px 0; font-size:16px; cursor:pointer;">
    Copy
  </button>
</div>

    <p>Share this link on Twitter or YouTube or Zealy to earn 500 PNTE for each referral!</p>
    <?php endif; ?>
  </div>
</div>

<div class="footer-box">
  © 2025 PNTE Airdrop. All rights reserved.
</div>

</body>
</html>