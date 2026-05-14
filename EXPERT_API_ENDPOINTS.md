# 🔌 Module Expert - API Endpoints

## 📌 Base URL
```
http://localhost:8000
```

## 🔐 Authentification
- **Rôle requis**: `ROLE_EXPERT`
- **Méthode**: Session-based (Form Login)

---

## 📊 Dashboard

### GET /expert/dashboard
Affiche le dashboard de l'expert avec les statistiques.

**Réponse**: HTML (Twig template)
```
Stats:
- analysesThisMonth: 5
- analysesTotal: 42
- conseilsTotal: 128
- pendingRequests: 3
```

---

## 📋 Demandes en Attente

### GET /expert/demandes-en-attente
Liste toutes les Analyses avec statut "en_attente".

**Réponse**: HTML (Twig template)
```
Affiche:
- Liste des demandes
- Pour chaque demande:
  - Demandeur
  - Ferme
  - Animal/Plante
  - Description
  - Image
  - Bouton "Prendre en charge"
```

### POST /expert/demande/{id}/prendre-en-charge
Expert prend en charge une demande.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Résultat**:
- Statut: "en_attente" → "en_cours"
- Technicien assigné: Expert courant
- Redirige vers: `/expert/analyse/{id}`

---

## 📊 Analyses

### GET /expert/analyses
Liste toutes les Analyses de l'expert.

**Paramètres** (Query):
- `search` (optionnel): Recherche par description
- `statut` (optionnel): Filtrer par statut

**Réponse**: HTML (Twig template)
```
Affiche:
- Liste des analyses
- Filtrage par statut
- Recherche
```

### GET /expert/analyse/{id}
Affiche les détails d'une Analyse.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: HTML (Twig template)
```
Affiche:
- Détails de l'Analyse
- Demandeur
- Ferme
- Animal/Plante
- Description
- Image
- Statut
- Boutons d'action
```

### GET /expert/analyse/new
Affiche le formulaire de création d'une Analyse.

**Réponse**: HTML (Twig template)

### POST /expert/analyse/new
Crée une nouvelle Analyse.

**Paramètres** (Form):
- `descriptionDemande`: Description du problème
- `imageUrl`: URL de l'image
- `ferme`: ID de la Ferme
- `animalCible`: ID de l'Animal (optionnel)
- `planteCible`: ID de la Plante (optionnel)

**Résultat**:
- Analyse créée
- Redirige vers: `/expert/analyses`

### GET /expert/analyse/{id}/edit
Affiche le formulaire de modification d'une Analyse.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: HTML (Twig template)

### POST /expert/analyse/{id}/edit
Modifie une Analyse.

**Paramètres**:
- `id` (URL): ID de l'Analyse
- Form data: Champs à modifier

**Résultat**:
- Analyse modifiée
- Redirige vers: `/expert/analyse/{id}`

### POST /expert/analyse/{id}/delete
Supprime une Analyse.

**Paramètres**:
- `id` (URL): ID de l'Analyse
- `_token` (Form): Token CSRF

**Résultat**:
- Analyse supprimée
- Conseils liés supprimés (cascade)
- Redirige vers: `/expert/analyses`

### POST /expert/analyse/{id}/status/{status}
Change le statut d'une Analyse.

**Paramètres**:
- `id` (URL): ID de l'Analyse
- `status` (URL): Nouveau statut (en_attente, en_cours, terminee, annulee)

**Résultat**:
- Statut modifié
- Redirige vers: `/expert/analyse/{id}`

---

## 🤖 Diagnostic IA

### POST /expert/analyse/{id}/diagnose
Effectue un diagnostic IA avec image.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Processus**:
1. Récupère l'image de l'Analyse
2. Envoie à GroqService.generateVisionDiagnostic()
3. Récupère les données météo
4. Stocke les résultats

**Résultat**:
- aiDiagnosisResult: JSON avec le diagnostic
- aiConfidenceScore: Score de confiance
- aiDiagnosisDate: Date du diagnostic
- weatherData: Données météo
- Redirige vers: `/expert/analyse/{id}`

### GET /expert/analyse/{id}/diagnose-text
Affiche le formulaire de diagnostic IA sans image.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: HTML (Twig template)
```
Formulaire:
- Textarea pour la description des symptômes
- Bouton "Diagnostiquer"
```

### POST /expert/analyse/{id}/diagnose-text
Effectue un diagnostic IA sans image.

**Paramètres**:
- `id` (URL): ID de l'Analyse
- `observation` (Form): Description des symptômes

**Processus**:
1. Récupère la description
2. Envoie à GroqService.generateTextDiagnostic()
3. Récupère les données météo
4. Stocke les résultats

**Résultat**:
- Même que Vision Diagnosis
- Redirige vers: `/expert/analyse/{id}`

### GET /expert/analyse/{id}/ai-result
Affiche le résultat du diagnostic IA.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: HTML (Twig template)
```
Affiche:
- Condition identifiée
- Symptômes
- Traitement
- Prévention
- Urgence
- Score de confiance
- Données météo
```

### POST /expert/analyse/{id}/diagnose/json
Effectue un diagnostic IA via API JSON.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: JSON
```json
{
  "success": true,
  "confidence": "92%",
  "condition": "Mildiou de la tomate",
  "message": "Diagnostic completed successfully"
}
```

**Erreur**: JSON
```json
{
  "success": false,
  "error": "No image available"
}
```

---

## 💡 Conseils

### GET /expert/conseils
Liste tous les Conseils de l'expert.

**Paramètres** (Query):
- `search` (optionnel): Recherche par description
- `priorite` (optionnel): Filtrer par priorité (HAUTE, MOYENNE, BASSE)

**Réponse**: HTML (Twig template)
```
Affiche:
- Liste des conseils
- Filtrage par priorité
- Recherche
```

### GET /expert/conseil/{id}
Affiche les détails d'un Conseil.

**Paramètres**:
- `id` (URL): ID du Conseil

**Réponse**: HTML (Twig template)
```
Affiche:
- Description du conseil
- Priorité
- Analyse associée
- Boutons d'action
```

### GET /expert/analyse/{id}/conseil/new
Affiche le formulaire de création d'un Conseil.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: HTML (Twig template)

### POST /expert/analyse/{id}/conseil/new
Crée un nouveau Conseil.

**Paramètres**:
- `id` (URL): ID de l'Analyse
- `descriptionConseil` (Form): Description du conseil
- `prioriteRaw` (Form): Priorité (HAUTE, MOYENNE, BASSE)

**Résultat**:
- Conseil créé
- Lié à l'Analyse
- Redirige vers: `/expert/conseils`

### GET /expert/conseil/{id}/edit
Affiche le formulaire de modification d'un Conseil.

**Paramètres**:
- `id` (URL): ID du Conseil

**Réponse**: HTML (Twig template)

### POST /expert/conseil/{id}/edit
Modifie un Conseil.

**Paramètres**:
- `id` (URL): ID du Conseil
- Form data: Champs à modifier

**Résultat**:
- Conseil modifié
- Redirige vers: `/expert/conseil/{id}`

### POST /expert/conseil/{id}/delete
Supprime un Conseil.

**Paramètres**:
- `id` (URL): ID du Conseil
- `_token` (Form): Token CSRF

**Résultat**:
- Conseil supprimé
- Redirige vers: `/expert/conseils`

---

## 📄 Export

### GET /expert/analyse/{id}/export/pdf
Exporte une Analyse en PDF.

**Paramètres**:
- `id` (URL): ID de l'Analyse

**Réponse**: PDF
```
Contient:
- Détails de l'Analyse
- Diagnostic IA
- Conseils générés
- Données météo
```

---

## 🔍 Recherche et Filtrage

### Recherche d'Analyses
```
GET /expert/analyses?search=maladie
    → Analyses contenant "maladie"
```

### Filtrage par Statut
```
GET /expert/analyses?statut=en_cours
    → Analyses avec statut "en_cours"
```

### Recherche de Conseils
```
GET /expert/conseils?search=fongicide
    → Conseils contenant "fongicide"
```

### Filtrage par Priorité
```
GET /expert/conseils?priorite=HAUTE
    → Conseils avec priorité HAUTE
```

### Combinaison
```
GET /expert/conseils?search=fongicide&priorite=HAUTE
    → Conseils contenant "fongicide" ET priorité HAUTE
```

---

## 📊 Statistiques

### Dashboard Stats
```
GET /expert/dashboard

Stats retournées:
- analysesThisMonth: Nombre d'analyses ce mois
- analysesTotal: Nombre total d'analyses
- conseilsTotal: Nombre total de conseils
- pendingRequests: Nombre de demandes en attente
```

---

## 🔐 Codes d'Erreur

### 200 OK
Requête réussie.

### 302 Found
Redirection (après création/modification/suppression).

### 403 Forbidden
- Expert n'est pas autorisé à voir/modifier cette ressource
- Rôle insuffisant

### 404 Not Found
- Ressource non trouvée
- Analyse/Conseil n'existe pas

### 405 Method Not Allowed
- Méthode HTTP non autorisée

### 500 Internal Server Error
- Erreur serveur
- Erreur IA (Groq API)
- Erreur météo (OpenWeather API)

---

## 🔗 Relations d'Entités

### Analyse
```
Analyse {
  id: int
  descriptionDemande: string
  imageUrl: string
  demandeur: User (Agriculteur)
  technicien: User (Expert)
  ferme: Ferme
  animalCible: Animal (optionnel)
  planteCible: Plante (optionnel)
  statut: string (en_attente, en_cours, terminee, annulee)
  aiDiagnosisResult: JSON
  aiConfidenceScore: string
  aiDiagnosisDate: DateTime
  weatherData: JSON
  weatherFetchedAt: DateTime
  dateAnalyse: DateTime
  conseils: Collection<Conseil>
}
```

### Conseil
```
Conseil {
  id: int
  descriptionConseil: string
  prioriteRaw: string (HAUTE, MOYENNE, BASSE)
  analyse: Analyse
}
```

---

## 📱 Exemples de Requêtes

### Créer une Analyse
```bash
curl -X POST http://localhost:8000/expert/analyse/new \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "descriptionDemande=Taches brunes&ferme=1&planteCible=2"
```

### Prendre en charge une demande
```bash
curl -X POST http://localhost:8000/expert/demande/5/prendre-en-charge
```

### Effectuer un diagnostic IA
```bash
curl -X POST http://localhost:8000/expert/analyse/5/diagnose
```

### Créer un conseil
```bash
curl -X POST http://localhost:8000/expert/analyse/5/conseil/new \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "descriptionConseil=Appliquer un fongicide&prioriteRaw=HAUTE"
```

### Exporter en PDF
```bash
curl -X GET http://localhost:8000/expert/analyse/5/export/pdf \
  -o analyse_5.pdf
```

---

## 🎯 Résumé des Endpoints

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| /expert/dashboard | GET | Dashboard |
| /expert/demandes-en-attente | GET | Demandes en attente |
| /expert/demande/{id}/prendre-en-charge | POST | Prendre en charge |
| /expert/analyses | GET | Liste des analyses |
| /expert/analyse/{id} | GET | Détails d'une analyse |
| /expert/analyse/new | GET/POST | Créer une analyse |
| /expert/analyse/{id}/edit | GET/POST | Modifier une analyse |
| /expert/analyse/{id}/delete | POST | Supprimer une analyse |
| /expert/analyse/{id}/status/{status} | POST | Changer le statut |
| /expert/analyse/{id}/diagnose | POST | Diagnostic IA (image) |
| /expert/analyse/{id}/diagnose-text | GET/POST | Diagnostic IA (texte) |
| /expert/analyse/{id}/ai-result | GET | Voir le résultat IA |
| /expert/analyse/{id}/diagnose/json | POST | Diagnostic IA (API) |
| /expert/conseils | GET | Liste des conseils |
| /expert/conseil/{id} | GET | Détails d'un conseil |
| /expert/analyse/{id}/conseil/new | GET/POST | Créer un conseil |
| /expert/conseil/{id}/edit | GET/POST | Modifier un conseil |
| /expert/conseil/{id}/delete | POST | Supprimer un conseil |
| /expert/analyse/{id}/export/pdf | GET | Exporter en PDF |

---

**Dernière mise à jour**: 6 mai 2026  
**Version**: 1.0  
**Statut**: Production
