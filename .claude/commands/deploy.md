# Déploiement Jurible

Déploie les changements sur le serveur O2switch.

## Si la connexion SSH échoue (IP non autorisée)

O2switch restreint l'accès SSH par IP. Si tu changes de lieu (maison, café, etc.) :

1. Va sur cPanel O2switch : https://dogfish.o2switch.net:2083
2. Section **Sécurité** → **Accès SSH**
3. Autorise ta nouvelle IP (ou clique "Ajouter mon IP actuelle")
4. Relance le déploiement

Pour connaître ton IP actuelle : `curl ifconfig.me`

## Étapes

1. Vérifie qu'il n'y a pas de modifications non commitées
2. Push sur GitHub si nécessaire
3. Connecte-toi en SSH et exécute le déploiement

## Commandes

```bash
# Vérifier le statut git
cd ~/Code/jurible && git status

# Push si nécessaire
cd ~/Code/jurible && git push

# Déployer le thème jurible sur jurible.com
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/jurible.com/wp-content/themes/jurible && cp -r themes/jurible ~/jurible.com/wp-content/themes/"

# Déployer le thème ecole.jurible sur ecole.jurible.com (si modifié)
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/ecole.jurible.com/wp-content/themes/ecole.jurible && cp -r themes/ecole.jurible ~/ecole.jurible.com/wp-content/themes/"

# Déployer les blocs React (si modifiés)
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/jurible.com/wp-content/plugins/jurible-blocks-react && cp -r plugins/jurible-blocks-react ~/jurible.com/wp-content/plugins/"
```

## Serveur

- **Hôte** : dogfish.o2switch.net
- **User** : aideauxtd
- **Repo sur serveur** : ~/jurible-repo
- **Site jurible.com** : ~/jurible.com
- **Site ecole.jurible.com** : ~/ecole.jurible.com
