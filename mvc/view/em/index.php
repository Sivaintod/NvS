<?php
$title = "Etat Major";

/* ---Header--- */
ob_start();
?>
<div class='background-img items'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<img class='float-left me-3' src='../images/<?php echo $image_em; ?>' width="80" height="60" alt="">
		<h2 class='mb-3'>Etat Major</h2>
		<?= $nb_persos_em ?> membres dans l'Etat Major :
		<?php if(isset($em_members) && !empty($em_members)):?>
		<ul class='list-group list-group-horizontal mt-1 mb-3'>
			<?php foreach($em_members as $member): ?>
			<li class='list-group-item list-group-item-primary'><?= $member['nom_perso']?> [<a href='evenement.php?infoid=<?= $member['id_perso']?>' class='text-decoration-none'><?= $member['id_perso']?></a>]</li>
			<?php endforeach;?>
		</ul>
		<?php endif; ?>
		<nav class='nav nav-tabs mb-3'>
			<a class="nav-link active" href="#">Validation compagnies</a>
			<span class="dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="dropdownMessagesMenu" role="button" data-toggle="dropdown" aria-expanded="false">
					Messages
				</a>
				<div class="dropdown-menu" aria-labelledby="dropdownMessagesMenu">
					<a class="dropdown-item" href="em_message.php?cible=camp">Message à son camp</a>
					<a class="dropdown-item" href="em_message.php?cible=compagnie">Message aux chefs de compagnie / section</a>
					<a class="dropdown-item" href="em_message.php?cible=em">Messages aux autres membres de l'EM</a>
				</div>
			</span>
			<a class="nav-link" href="jouer.php">Retour au jeu</a>
		</nav>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(isset($error)): ?>
<p class='alert alert-danger'>
	<?= $error ?>
</p>
<?php endif; ?>
<?php if(isset($_SESSION['flash'])&& !empty($_SESSION['flash'])): ?>
<div class="row">
	<div class='col'>
		<div class='alert alert-<?= $_SESSION['flash']['class'] ?>'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle'><?= $_SESSION['flash']['message'] ?></span>
		</div>
	</div>
</div>
<?php endif ?>
<div class="row">
	<div class="col">
		<div class="card shadow">
			<div class='card-header'>
				<h4>Gestion des compagnies</h4>
			</div>
			<div class="card-body">
				<h5 class="card-title">Demandes de création de compagnie</h5>
				<?php if(isset($company_demands) && !empty($company_demands)): ?>
				<table class="table align-middle table-striped">
					<caption>Demandes de compagnie</caption>
					<thead class='table-light'>
						<tr>
							<th scope="col">Demandeur</th>
							<th class='w-25' scope="col">Nom</th>
							<th class='text-center' scope="col">Description</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($company_demands as $demand): ?>
						<tr>
							<th scope="row" class='p-4'>
									<?= $demand["nom_perso"]?> [<?=$demand["id_perso"]?>]
							</th>
							<td class='p-4'>
								<p>
									<?= $demand["nom_compagnie"] ?>
								</p>
							</td>
							<td class='w-75 p-4'>
								<p>
									<span class='fw-semibold'>Résumé : </span><br>
									<?= $demand["description_compagnie"]?>
								</p>
							</td>				
							<td class='p-4 align-middle'>
								<form name='company_vote' method='post' action='etat_major.php'>
									<input type="hidden" name="id" value="<?= $demand['id_em_creer_compagnie']?>">
									<div class="form-check mb-2">
										<input class="form-check-input vote-input" type="radio" name="Comp<?= $demand['id_em_creer_compagnie']?>voteOption" id="Comp<?= $demand['id_em_creer_compagnie']?>voteOption1" value="1">
										<label class="form-check-label btn btn-success w-100" for="Comp<?= $demand['id_em_creer_compagnie']?>voteOption1">Pour</label>
									</div>
									<div class="form-check">
										<input class="form-check-input vote-input" type="radio" name="Comp<?= $demand['id_em_creer_compagnie']?>voteOption" id="Comp<?= $demand['id_em_creer_compagnie']?>voteOption2" value="0">
										<label class="form-check-label btn btn-danger w-100" for="Comp<?= $demand['id_em_creer_compagnie']?>voteOption2">Contre</label>
									</div>
									<button type='submit' class='w-100 btn btn-primary mt-3'>Voter</button>
								</form>
							</td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				<?php else: ?>
					<p class=''>Aucune compagnie créée. <a class='btn btn-success ms-4' href='?action=create' >Créer une compagnie</a></p>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>