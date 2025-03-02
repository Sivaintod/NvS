document.addEventListener('DOMContentLoaded', function() {
    console.log( "DOM chargé" );
	
	// actualisation du captcha
	$('#reload_captcha').click(function(){
		$("#captcha_img").attr("src", "../captcha.php?"+(new Date()).getTime());
	})

	//aperçu d'une image sélectionnée dans un formulaire
	var imgInput = document.getElementById('imgUpload');
	var imgPreview150 = document.getElementById('imgPreview150');
	var imgPreview40 = document.getElementById('imgPreview40');
	var imgPreviewName = document.getElementById('imgPreviewName');

	if(imgInput!==null){
		imgInput.addEventListener('change', function(event) {
			clearImgPreview();

			var file = event.target.files[0];
			if (file) {
				imgPreviewName.textContent = file.name;
				var reader = new FileReader();

				reader.onload = function(e) {
					var img150 = document.createElement('img');
					img150.src = e.target.result;
					img150.style.maxWidth = '150px';
					img150.style.maxHeight = '150px';
					imgPreview150.appendChild(img150);
					
					var img40 = document.createElement('img');
					img40.src = e.target.result;
					img40.style.maxWidth = '40px';
					img40.style.maxHeight = '40px';
					imgPreview40.appendChild(img40);
				}

				reader.readAsDataURL(file);
			}
		});

		function clearImgPreview() {
			imgPreview150.innerHTML = '';
			imgPreview40.innerHTML = '';
		}
	};
	
	// récupération des détails de compte
	const detailsBtns = document.querySelectorAll(".showDetailsBtn");
	if(detailsBtns!==null){
		const detailsTitle = document.getElementById("showDetailsDescribe");
		const detailsContent = document.getElementById("showDetailsContent");
		
		detailsBtns.forEach(button => {
			button.addEventListener("click", function() {
				
				detailsContent.innerHTML = '';
				const idVal = parseInt(this.getAttribute('data-nvs-id'));
				detailsTitle.textContent = typeof idVal;
				
				fetch('bank.php?action=AccountDetails&id='+idVal,{
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Erreur, veuillez recommencer. Si le problème persiste, merci de contacter les administrateurs');
					}
					return response.json();
				})
				.then(data => {
					if (data.error) {
						console.log(data);
						detailsContent.textContent = data.error;
					} else {
						console.log(data);
						detailsTitle.innerHTML = data[0]['nom_perso']+' ['+data[0]['id_perso']+']';
						// console.log(data[1]);
						
						// detailsContent.insertRow();
						// detailsContent.insertRow();
						
						data[1].forEach( function (operation) {
							console.log(operation['operation']);
							
							switch(operation['operation']){
								case 0:
									var ope = "dépôt";
									var symbol = '+';
									var classColor = 'table-success';
									break;
								case 1:
									var ope = "retrait";
									var symbol = '-';
									var classColor = 'table-danger';
									break;
								case 2:
									var ope = "Emprunt";
									var symbol = '-';
									var classColor = 'table-danger';
									break;
								case 3:
									var ope = "Remboursement d'emprunt";
									var symbol = '+';
									var classColor = 'table-success';
									break;
								case 4:
									var ope = "virement";
									var symbol = '';
									var classColor = 'table-light';
									break;
							}
							var row = detailsContent.insertRow();
							var cat = row.insertCell(0);
							var amount = row.insertCell(1);
							var detail = row.insertCell(2);
							var date = row.insertCell(3);
							
							row.classList.add(classColor);
							amount.classList.add('text-end');
							amount.classList.add('pe-5');
							date.classList.add('text-center');
							
							cat.innerHTML = ope;
							amount.innerHTML = symbol+' '+operation['montant_transfert']+' thune(s)';
							detail.innerHTML = operation['details'];
							
							var date_log = new Date(operation['date_log'])
							.toLocaleDateString('fr-FR',{
								hour: 'numeric',
								minute: 'numeric',
								second: 'numeric'
							})
							date.innerHTML = date_log;
						});
					}
				})
				.catch(error => {
					console.log(error);
					detailsContent.textContent = error.message;
				});

				console.log('Récupération en cours...');
			});
		});
	};
	
	// gestion personnalisée des modals
	const quitCompanyModal = document.getElementById('quitCompModal');
	if (quitCompanyModal) {
	  quitCompanyModal.addEventListener('show.bs.modal', event => {
		// Button that triggered the modal
		const button = event.relatedTarget;
		// Extract info from data-bs-* attributes
		const demandType = button.getAttribute('data-bs-demandtype');
		
		switch(demandType){
			case 'cancel':
			title_custom = 'Annuler la demande pour ';
			content_custom = "d'annuler la demande pour";
			details_custom = "";
			btn_custom = 'Confirmer';
			break;
			case 'quit':
			title_custom = 'Quitter';
			content_custom = 'de quitter';
			details_custom = 'Votre départ devra être validé par un responsable de la compagnie.';
			btn_custom = 'Quitter';
			break;
		}

		// Update the modal's content.
		const modalTitle = quitCompanyModal.querySelector('.customModalTitle');
		const modalContent = quitCompanyModal.querySelector('.customModalContent');
		const modalDetails = quitCompanyModal.querySelector('.customModalDetails');
		const modalBtn = quitCompanyModal.querySelector('.customModalBtn');

		modalTitle.textContent = title_custom;
		modalContent.textContent = content_custom;
		modalDetails.textContent = details_custom;
		modalBtn.textContent = btn_custom;
	  })
	}
	
	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});