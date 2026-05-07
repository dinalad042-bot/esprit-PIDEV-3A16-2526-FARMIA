# 🎯 Module Expert - Cas d'Usage

## 📌 Cas d'Usage Principaux

### 1. Expert se connecte
```
URL: http://localhost:8000/login
Email: expert@example.com
Mot de passe: password
Rôle: ROLE_EXPERT

Résultat: Redirige vers /expert/dashboard
```

### 2. Expert voit le dashboard
```
URL: GET /expert/dashboard

Affiche:
- Analyses ce mois: 5
- Analyses total: 42
- Conseils total: 128
- Demandes en attente: 3 (avec badge rouge)

Actions rapides:
- Nouvelle Analyse
- Créer Rapport
- Voir les demandes en attente
```

### 3. Expert voit les demandes en attente
```
URL: GET /expert/demandes-en-attente

Affiche:
- Liste des Analyses avec statut "en_attente"
- Pour chaque demande:
  - Demandeur (Agriculteur)
  - Ferme concernée
  - Animal/Plante ciblé
  - Description du problème
  - Image (si disponible)
  - Bouton "Prendre en charge"
```

### 4. Expert prend en charge une demande
```
URL: POST /expert/demande/{id}/prendre-en-charge

Avant:
- Analyse.statut = "en_attente"
- Analyse.technicien = NULL

Après:
- Analyse.statut = "en_cours"
- Analyse.technicien = Expert (User courant)

Redirige vers: GET /expert/analyse/{id}
```

### 5. Expert voit les détails d'une analyse
```
URL: GET /expert/analyse/{id}

Affiche:
- Demandeur (Agriculteur)
- Ferme concernée
- Animal/Plante ciblé
- Description du problème
- Image (si disponible)
- Statut actuel
- Boutons:
  - Diagnostiquer (IA avec image)
  - Diagnostiquer (IA sans image)
  - Créer Conseil
  - Exporter PDF
  - Modifier
  - Supprimer
```

### 6. Expert effectue un diagnostic IA avec image
```
URL: POST /expert/analyse/{id}/diagnose

Processus:
1. Récupère l'image de l'Analyse
2. Envoie à GroqService.generateVisionDiagnostic()
3. Récupère les données météo (WeatherService)
4. Stocke les résultats:
   - aiDiagnosisResult (JSON)
   - aiConfidenceScore
   - aiDiagnosisDate
   - weatherData
5. Redirige vers GET /expert/analyse/{id}

Résultat IA:
- condition: Condition identifiée
- symptoms: Symptômes détectés
- treatment: Traitement recommandé
- prevention: Prévention
- urgency: Niveau d'urgence
- confidence: Score de confiance (0-100%)
```

### 7. Expert effectue un diagnostic IA sans image
```
URL: GET /expert/analyse/{id}/diagnose-text
     POST /expert/analyse/{id}/diagnose-text

Processus:
1. Affiche un formulaire pour entrer une description
2. Expert entre les symptômes/observations
3. Envoie à GroqService.generateTextDiagnostic()
4. Récupère les données météo
5. Stocke les résultats (même que Vision)
6. Affiche le résultat IA

Résultat: Même que Vision Diagnosis
```

### 8. Expert voit le résultat du diagnostic IA
```
URL: GET /expert/analyse/{id}/ai-result

Affiche:
- Condition identifiée
- Symptômes détectés
- Traitement recommandé
- Prévention
- Niveau d'urgence
- Score de confiance
- Données météo
```

### 9. Expert crée un conseil
```
URL: GET /expert/analyse/{id}/conseil/new
     POST /expert/analyse/{id}/conseil/new

Formulaire:
- Description du conseil (textarea)
- Priorité (HAUTE, MOYENNE, BASSE)

Processus:
1. Expert remplit le formulaire
2. Crée un Conseil lié à l'Analyse
3. Stocke: descriptionConseil, prioriteRaw, analyse
4. Redirige vers GET /expert/conseils

Résultat:
- Conseil créé
- Lié à l'Analyse
- Visible dans la liste des conseils
```

### 10. Expert crée plusieurs conseils
```
Processus:
1. Expert crée Conseil 1 (Priorité HAUTE)
2. Expert crée Conseil 2 (Priorité MOYENNE)
3. Expert crée Conseil 3 (Priorité BASSE)

Résultat:
- 3 Conseils liés à la même Analyse
- Chacun avec sa priorité
- Agriculteur reçoit tous les conseils
```

### 11. Expert change le statut d'une analyse
```
URL: POST /expert/analyse/{id}/status/{status}

Statuts possibles:
- en_attente
- en_cours
- terminee
- annulee

Exemple:
POST /expert/analyse/5/status/terminee
    → Analyse.statut = "terminee"
```

### 12. Expert voit tous ses conseils
```
URL: GET /expert/conseils

Affiche:
- Liste de tous les Conseils de l'expert
- Filtrage par priorité
- Recherche par description
- Pour chaque conseil:
  - Description
  - Priorité (avec badge coloré)
  - Analyse associée
  - Boutons: Voir, Modifier, Supprimer
```

### 13. Expert modifie un conseil
```
URL: GET /expert/conseil/{id}/edit
     POST /expert/conseil/{id}/edit

Formulaire:
- Description du conseil
- Priorité

Processus:
1. Expert modifie le formulaire
2. Sauvegarde les modifications
3. Redirige vers GET /expert/conseil/{id}
```

### 14. Expert supprime un conseil
```
URL: POST /expert/conseil/{id}/delete

Processus:
1. Expert clique "Supprimer"
2. Confirmation CSRF
3. Conseil supprimé
4. Redirige vers GET /expert/conseils
```

### 15. Expert exporte une analyse en PDF
```
URL: GET /expert/analyse/{id}/export/pdf

Résultat:
- Génère un PDF avec:
  - Détails de l'Analyse
  - Diagnostic IA
  - Conseils générés
  - Données météo
- Télécharge le PDF
```

### 16. Expert recherche une analyse
```
URL: GET /expert/analyses?search=maladie

Affiche:
- Analyses contenant "maladie" dans la description
- Filtrage par statut
```

### 17. Expert filtre les conseils par priorité
```
URL: GET /expert/conseils?priorite=HAUTE

Affiche:
- Conseils avec priorité HAUTE
- Filtrage par recherche
```

### 18. Expert voit les statistiques
```
Dashboard:
- Analyses ce mois: countByTechnicienThisMonth()
- Analyses total: countByTechnicien()
- Conseils total: countByTechnicien()
- Demandes en attente: countPendingRequests()

Conseils:
- Par priorité: getPriorityStats()
- Par expert: countByTechnicien()
```

---

## 🔄 Flux Complet (Exemple Réel)

### Scénario: Agriculteur a un problème de maladie sur ses tomates

#### Étape 1: Agriculteur crée une Analyse
```
Agriculteur → /agricole/dashboard
           → Crée une Analyse
           → Sélectionne Ferme: "Ferme du Soleil"
           → Sélectionne Plante: "Tomate"
           → Description: "Taches brunes sur les feuilles"
           → Télécharge une image
           → Clique "Créer"
           
Résultat:
- Analyse créée
- Statut: "en_attente"
- Demandeur: Agriculteur
- Technicien: NULL
```

#### Étape 2: Expert reçoit la notification
```
Expert → /expert/dashboard
      → Voit "1 demande en attente"
      → Clique sur le badge
      → Redirige vers /expert/demandes-en-attente
```

#### Étape 3: Expert voit la demande
```
Expert → /expert/demandes-en-attente
      → Voit la demande:
        - Demandeur: Jean Dupont
        - Ferme: Ferme du Soleil
        - Plante: Tomate
        - Description: "Taches brunes sur les feuilles"
        - Image: [Affichée]
      → Clique "Prendre en charge"
```

#### Étape 4: Expert prend en charge
```
POST /expert/demande/5/prendre-en-charge

Résultat:
- Analyse.statut = "en_cours"
- Analyse.technicien = Expert
- Redirige vers /expert/analyse/5
```

#### Étape 5: Expert voit les détails
```
Expert → /expert/analyse/5
      → Voit tous les détails
      → Clique "Diagnostiquer"
```

#### Étape 6: Expert effectue le diagnostic IA
```
POST /expert/analyse/5/diagnose

Processus:
1. Envoie l'image à Groq
2. Groq retourne: "Mildiou de la tomate"
3. Récupère la météo: "Humidité 85%, Température 22°C"
4. Stocke les résultats

Résultat IA:
- condition: "Mildiou de la tomate"
- symptoms: "Taches brunes, feuilles jaunes"
- treatment: "Appliquer un fongicide"
- prevention: "Améliorer la ventilation"
- urgency: "Haute"
- confidence: "92%"
```

#### Étape 7: Expert crée des conseils
```
Expert → /expert/analyse/5
      → Clique "Créer Conseil"
      
Conseil 1:
- Description: "Appliquer immédiatement un fongicide à base de cuivre"
- Priorité: HAUTE 🔴

Conseil 2:
- Description: "Améliorer la ventilation entre les plants"
- Priorité: MOYENNE 🟡

Conseil 3:
- Description: "Surveiller les autres plants pour détecter la maladie"
- Priorité: BASSE 🟢

Résultat:
- 3 Conseils créés
- Statut Analyse: "terminee"
```

#### Étape 8: Agriculteur reçoit les conseils
```
Agriculteur → Notification: "Conseils disponibles pour votre demande"
           → /agricole/dashboard
           → Voit les 3 Conseils:
             - HAUTE: Appliquer immédiatement un fongicide...
             - MOYENNE: Améliorer la ventilation...
             - BASSE: Surveiller les autres plants...
           → Lit les recommandations
           → Applique les conseils
```

---

## 🔐 Cas d'Usage de Sécurité

### 1. Expert essaie de voir une analyse d'un autre expert
```
Expert A → GET /expert/analyse/5
        → Analyse.technicien = Expert B
        
Résultat: 403 Forbidden
Message: "Vous n'êtes pas autorisé à voir cette analyse."
```

### 2. Expert essaie de modifier un conseil d'un autre expert
```
Expert A → POST /expert/conseil/10/edit
        → Conseil.analyse.technicien = Expert B
        
Résultat: 403 Forbidden
Message: "Vous n'êtes pas autorisé à modifier ce conseil."
```

### 3. Agriculteur essaie d'accéder au dashboard expert
```
Agriculteur → GET /expert/dashboard
           → Rôle: ROLE_AGRICOLE
           
Résultat: 403 Forbidden
Message: "Accès refusé"
```

### 4. Utilisateur non authentifié essaie d'accéder
```
Utilisateur → GET /expert/dashboard
          → Pas authentifié
          
Résultat: Redirige vers /login
```

---

## 📊 Cas d'Usage de Statistiques

### 1. Expert voit ses statistiques
```
Dashboard:
- Analyses ce mois: 5
- Analyses total: 42
- Conseils total: 128
- Demandes en attente: 3

Calcul:
- countByTechnicienThisMonth($expertId)
- countByTechnicien($expertId)
- countByTechnicien($expertId) (pour conseils)
- countPendingRequests()
```

### 2. Expert voit les statistiques des conseils
```
GET /expert/conseils

Stats:
- Conseils HAUTE: 45
- Conseils MOYENNE: 52
- Conseils BASSE: 31

Calcul:
- getPriorityStats()
```

---

## 🚀 Cas d'Usage Avancés

### 1. Expert crée une analyse manuellement
```
URL: GET /expert/analyse/new
     POST /expert/analyse/new

Formulaire:
- Ferme
- Animal/Plante cible
- Description
- Image

Résultat:
- Analyse créée
- Technicien: Expert (créateur)
- Demandeur: Expert (créateur)
```

### 2. Expert modifie une analyse
```
URL: GET /expert/analyse/{id}/edit
     POST /expert/analyse/{id}/edit

Formulaire:
- Description
- Résultat technique
- Statut

Résultat:
- Analyse modifiée
```

### 3. Expert supprime une analyse
```
URL: POST /expert/analyse/{id}/delete

Résultat:
- Analyse supprimée
- Tous les Conseils liés supprimés (cascade delete)
```

### 4. Expert exporte une analyse en PDF
```
URL: GET /expert/analyse/{id}/export/pdf

Résultat:
- PDF généré avec:
  - Détails de l'Analyse
  - Diagnostic IA
  - Conseils générés
  - Données météo
- Téléchargement du PDF
```

---

## 📱 Cas d'Usage API

### 1. Diagnostic IA via API JSON
```
POST /expert/analyse/{id}/diagnose/json

Résultat JSON:
{
  "success": true,
  "confidence": "92%",
  "condition": "Mildiou de la tomate",
  "message": "Diagnostic completed successfully"
}
```

---

## 🎯 Résumé des Cas d'Usage

| Cas d'Usage | URL | Méthode | Résultat |
|-------------|-----|--------|---------|
| Se connecter | /login | POST | Redirige vers /expert/dashboard |
| Voir le dashboard | /expert/dashboard | GET | Affiche les stats |
| Voir les demandes | /expert/demandes-en-attente | GET | Liste les demandes |
| Prendre en charge | /expert/demande/{id}/prendre-en-charge | POST | Statut: en_cours |
| Voir une analyse | /expert/analyse/{id} | GET | Affiche les détails |
| Diagnostic IA (image) | /expert/analyse/{id}/diagnose | POST | Stocke le diagnostic |
| Diagnostic IA (texte) | /expert/analyse/{id}/diagnose-text | POST | Stocke le diagnostic |
| Voir le résultat IA | /expert/analyse/{id}/ai-result | GET | Affiche le résultat |
| Créer un conseil | /expert/analyse/{id}/conseil/new | POST | Crée le conseil |
| Voir les conseils | /expert/conseils | GET | Liste les conseils |
| Modifier un conseil | /expert/conseil/{id}/edit | POST | Modifie le conseil |
| Supprimer un conseil | /expert/conseil/{id}/delete | POST | Supprime le conseil |
| Exporter en PDF | /expert/analyse/{id}/export/pdf | GET | Télécharge le PDF |

---

**Dernière mise à jour**: 6 mai 2026  
**Version**: 1.0  
**Statut**: Production
