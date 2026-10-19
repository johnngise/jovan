# Configuration de l’envoi du formulaire de contact

Le formulaire envoie les messages avec PHPMailer via Gmail SMTP. Le serveur,
le port TLS et le compte d’envoi/réception `johnngise@gmail.com` sont
préconfigurés dans `config/contact-mail.local.php`, qui est exclu de Git.

Pour activer l’envoi :

1. Dans le compte Google `johnngise@gmail.com`, activez la validation en
   deux étapes si ce n’est pas déjà fait, puis créez un mot de passe
   d’application dans les paramètres de sécurité du compte.
2. Ouvrez `config/contact-mail.local.php` et remplacez la valeur vide de
   `ORPAIS_SMTP_PASSWORD` par ce mot de passe d’application (sans espaces).
3. Enregistrez puis envoyez un message de test depuis la page Contact.

Ne mettez jamais le mot de passe Google principal dans ce fichier, ne le
commitez pas et ne le partagez pas dans une conversation. Le mot de passe
d’application est un secret ; le fichier local est ignoré par Git.

Les variables d’environnement correspondantes peuvent remplacer les valeurs
locales si nécessaire :

| Variable | Description | Exemple Gmail |
| --- | --- | --- |
| `ORPAIS_SMTP_HOST` | Serveur SMTP | `smtp.gmail.com` |
| `ORPAIS_SMTP_PORT` | Port SMTP | `587` |
| `ORPAIS_SMTP_ENCRYPTION` | Chiffrement (`tls` ou `ssl`) | `tls` |
| `ORPAIS_SMTP_USERNAME` | Adresse/identifiant SMTP | `johnngise@gmail.com` |
| `ORPAIS_SMTP_PASSWORD` | Mot de passe d’application Gmail | À définir localement |
| `ORPAIS_MAIL_FROM` | Adresse d’expédition autorisée par Gmail | `johnngise@gmail.com` |
| `ORPAIS_CONTACT_EMAIL` | Adresse destinataire (facultatif) | `johnngise@gmail.com` |

Le mot de passe d’application n’est pas inclus dans le dépôt. Si l’envoi
échoue après sa configuration, consultez les journaux PHP/Apache et vérifiez
également le dossier Spam du compte destinataire.
