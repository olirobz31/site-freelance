# Formulaire multi-étapes premium — Pack vendu

## Contenu
- index.html
- style.css
- script.js
- send.php
- merci.html

## Installation rapide
1. Copier les fichiers sur l'hébergement du client.
2. Modifier `send.php` en mettant ton adresse email à `$to`.
3. (Recommandé) Installer PHPMailer via Composer pour envoi SMTP :
   - `composer require phpmailer/phpmailer`
   - Configurer les paramètres SMTP dans `send.php`.
4. Vérifier les permissions pour uploader des fichiers (si activé).

## Personnalisation
- Logo : remplacer `logo-placeholder.svg`.
- Couleurs : modifier les variables CSS dans `:root`.
- Textes : éditer `index.html` / `merci.html`.

## Points techniques / sécurité
- Honeypot anti-spam inclus (`website`).
- Valider/sanitiser côté serveur si tu ajoutes d'autres champs.
- Si tu vends, fournis un guide d'installation (screenshots + étapes SMTP).

## Licence & support
- Tu peux revendre ce template; fournis 1 ticket d'installation gratuit pour chaque vente (optionnel).
