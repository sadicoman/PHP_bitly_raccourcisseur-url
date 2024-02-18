<?php

define('BASE_URL', 'http://localhost/projets/Projet_3_raccourcisseurURL/');

try {
	$bdd = new PDO('mysql:host=localhost;dbname=bitly;charset=utf8', 'root', '');
} catch (Exception $e) {
	die('Erreur : ' . $e->getMessage());
}

// Vérifie si un raccourci a été reçu via l'URL
if (isset($_GET['q'])) {
	$shortcut = htmlspecialchars($_GET['q']);
	$req = $bdd->prepare('SELECT * FROM links WHERE shortcut = ?');
	$req->execute(array($shortcut));
	$result = $req->fetch();

	if ($result) {
		header('Location: ' . $result['url']);
		exit();
	} else {
		header('Location: ' . BASE_URL . '?error=true&message=Adresse url non connue');
		exit();
	}
}

// Vérifie si un formulaire a été soumis
if (isset($_POST['url'])) {
	$url = $_POST['url'];

	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		header('Location: ' . BASE_URL . '?error=true&message=Adresse url non valide');
		exit();
	}

	// Génère un raccourci unique
	$shortcut = uniqid(rand(), true);

	// Vérifie l'unicité du raccourci
	$req = $bdd->prepare('SELECT COUNT(*) AS x FROM links WHERE shortcut = ?');
	$req->execute(array($shortcut));
	$result = $req->fetch();

	while ($result['x'] != 0) {
		$shortcut = uniqid(rand(), true);
		$req->execute(array($shortcut));
		$result = $req->fetch();
	}

	// Vérifie si l'URL a déjà été raccourcie
	$req = $bdd->prepare('SELECT * FROM links WHERE url = ?');
	$req->execute(array($url));
	$result = $req->fetch();

	if ($result) {
		header('Location: ' . BASE_URL . '?short=' . $result['shortcut']);
		exit();
	} else {
		// Insère l'URL et le raccourci dans la base de données
		$req = $bdd->prepare('INSERT INTO links(url, shortcut) VALUES(?, ?)');
		$req->execute(array($url, $shortcut));

		header('Location: ' . BASE_URL . '?short=' . $shortcut);
		exit();
	}
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Raccourcisseur d'url express</title>
	<link rel="stylesheet" type="text/css" href="design/default.css">
	<link rel="icon" type="image/png" href="pictures/favico.png">
</head>

<body>
	<section id="hello">
		<div class="container">
			<header>
				<img src="pictures/logo.png" alt="logo" id="logo">
			</header>
			<div>
				<h1>Une url longue ? Raccourcissez-là.</h1>
			</div>
			<h2>Largement meilleur et plus court que les autres.</h2>
			<form class="form" method="post" action="">
				<input type="url" name="url" placeholder="Collez un lien à raccourcir">
				<input type="submit" value="Raccourcir">
			</form>
			<?php
			if (isset($_GET['error']) && isset($_GET['message'])) { ?>
				<div class="center">
					<div id="result">
						<b><?php echo htmlspecialchars($_GET['message']); ?></b>
					</div>
				</div>
			<?php } else if (isset($_GET['short'])) { ?>
				<div class="center">
					<div id="result">
						<b>URL RACCOURCIE : </b>
						<div class="url__container"><a href="<?php echo BASE_URL . '?q=' . htmlspecialchars($_GET['short']); ?>"><?php echo BASE_URL . '?q=' . htmlspecialchars($_GET['short']); ?></a></div>
					</div>
				</div>
			<?php } ?>
		</div>
	</section>
	<section id="brands">
		<div class="container">
			<h3>Ces marques nous font confiance</h3>
			<ul class="brand-list">
				<li><img src="pictures/1.png" alt="Marque 1" class="brand-logo"></li>
				<li><img src="pictures/2.png" alt="Marque 2" class="brand-logo"></li>
				<li><img src="pictures/3.png" alt="Marque 3" class="brand-logo"></li>
				<li><img src="pictures/4.png" alt="Marque 4" class="brand-logo"></li>
			</ul>
		</div>
	</section>

	<footer>
		<div class="container">
			<div><img src="pictures/logo2.png" alt="logo" id="logo"></div>
			<p>© 2023 - Raccourcisseur d'url express</p>
		</div>
	</footer>
</body>

</html>