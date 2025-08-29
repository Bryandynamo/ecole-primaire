# Import de la base SQL

Placez votre dump complet ici sous le nom `ecoleprimaire.sql` (ou indiquez un autre chemin à la commande).

## Commandes utiles

- Import simple (sans recréer le schéma) :

```bash
php artisan db:import-sql database/dumps/ecoleprimaire.sql
```

- Import après recréation des tables (efface et recrée le schéma) :

```bash
php artisan db:import-sql database/dumps/ecoleprimaire.sql --fresh
```

> Attention: `--fresh` supprime toutes les tables (équivalent à `migrate:fresh`).
