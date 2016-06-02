Système de tâches à faire

Les tâches à faire sont un système permettant de suivre les actions à faire pour régulariser une entité. Cette entité peut être un adhérent, une transaction, un abonnement... qui est en situation irrégulière : il manque des informations de contact, ou une échéance n'a pas été réglée...

1. Types de tâches
le token désigne toujours l'entité de plus bas niveau désignée (USR désigne un utilisateur, TRA désigne la transaction, et il est possible de récupérer l'utilisateur de cette transaction à partir des champs dans la base)

USR : utilisateurs

TRA : transactions

PRD : produit

MAT : échéance

Ces tokens sont très généraux et aideront simplement l'application à les retrouver et les afficher au bon endroit. Le contenu d'une tâche est totalement décidé par l'utilisateur.


Exemples de fonctionnement :
La tâche USR 10599 est une tâche concernant l'utilisateur numéro 10599.
La tâche PRD 375 est une tâche concernant le produit numéro 375.

2. Indications de titre et description
Pour chaque tâche, il est possible d'indiquer un titre et une description. Lorsqu'une la création d'une tâche est démarrée, un titre est proposé par défaut en fonction du token sélectionné. Ce titre contient des expressions définies qui sont interprétées par l'application comme !USER!, qui est une variable qui affichera le nom de la personne concernée.

Exemple pour la tâche USR : "Il manque des informations pour !USER!". Il peut être intéressant de conserver les variables car elles gagnent du sens lorsqu'elles sont interprétées par l'application.

La description n'est pas prédéfinie.


