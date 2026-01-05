# Changelog plugin BSBLAN

# 05/01/2026

- Remplacement event par checkAndUpdateCmd pour eviter répétition des valeurs dans history
- Déplacement de la documentation dans un repository github séparé afin de pouvoir mettre à jour la documentation sans générer un update du plugin

# 27/01/2025

- Gestion des commandes de mise à jour via JSON ou url /S (voir documentation)

# 10/11/2024

- Mise à jour de la documentation

# 07/11/2024

- Passage des methodes cron en static pour éviter erreur en PHP 8

# 06/08/2024

- Possibilité de soumettre plusieurs fois une commande qui aurait échoué

Suite au passage à Debian 11, j'ai constaté que j'obtenais des timeouts après avoir soumis des commandes au BSBLAN (cela ne se produisait pas en Debian 10 et je ne vois pas où chercher pour régler le problème au niveau OS). En soumettant la commande à nouveau, celle-ci passe en général sans problème. C'est pourquoi j'ai ajouté au niveau de chaque équipement une option 'Nombre d'essais' qui permettait soumettre la commande plusieurs fois.

# 28/04/2024

- Mise à jour mineure de la documentation

# 25/02/2024

- Passage des noms de commande de 40 à 100 caracteres
- Mise à jour documentation

# 28/12/2023

- Ajout d'une commande refresh (celle-ci est créée lorsque l'on sauvegarde l'équipement)
  
# 21/10/2023

- Update debug message
- Update index et changelog pour la version beta

# 01/08/2023

- Ajout d'un timeout pour les requetes http

# 10/07/2023

- Initial load

