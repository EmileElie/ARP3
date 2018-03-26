# ARPA3


Première étape :

	- Ajout du dossier 'quantityleftupdate' dans /modules
	- Ajout du fichier 'quantityleftupdate.html' dans /mails

Deuxième étape :

	- Modification du fichier blockpermanentlinks.css
	-- Ajout 'list-style-type: circle;' à ul#header_links
	-- Suppression du 'float: right;' et de la bordure de #header_links li

Troisième étape :

	- Ajout du code de track dans le fichier \themes\default-bootstrap\header.tpl (juste avant la balise </head>)
	- Modification des 'xxx' en {$total_to_pay}

Quatrième étape :

	- Modification de la fonction hookDisplayHomeTab
	-- Modification de la variable cache_specials
	--- Ajout de paramètres à l'appel de fonction getPricesDrop() : 
		$order_by = 'date_add'
		$order_way = 'DESC'




Emile Youssef