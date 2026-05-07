# 🔬 EXPERT MODULE - COMPLETE TECHNICAL ANALYSIS

## 📋 EXECUTIVE SUMMARY

This is a **Symfony 6.4 PHP agricultural platform (FarmAI)** with AI-powered diagnostic capabilities for agricultural experts. The expert module enables agricultural technicians to diagnose plant/animal diseases using AI vision and text analysis, manage analyses, and provide recommendations (conseils) to farmers.

---

## 🏗️ TECH STACK

### **Backend Framework**
- **Symfony 6.4** (PHP 8.1+)
- **Doctrine ORM 2.18** (MySQL/MariaDB)
- **Twig** templating engine

### **AI Integration**
- **Primary AI Service**: Groq Cloud API
  - Model: `llama-3.2-11b-vision-preview` (vision diagnostics)
  - Model: `llama-3.3-70b-versatile` (chat/text diagnostics)
  - API: `https://api.groq.com/openai/v1/chat/completions`
- **Weather API**: OpenWeather API integration
- **Python API**: Flask-based facial recognition service (port 5000)

### **Database**
- **MySQL/MariaDB** (configured in `.env`)
- Database name: `farmai`
- Connection: `mysql://root:@127.0.0.1:3306/farmai`

### **External Services**
- Groq AI (LLaMA models for vision + text diagnostics)
- OpenWeather API (contextual weather data for farm locations)
- Python Flask API (facial recognition, runs on port 5000)

---

## 👥 USER ROLES & PERMISSIONS

### **Available Roles**
1. **ROLE_ADMIN** - System administrators
2. **ROLE_EXPERT** - Agricultural experts/technicians (THIS MODULE)
3. **ROLE_AGRICOLE** - Farmers/agricultural users
4. **ROLE_FOURNISSEUR** - Suppliers

### **Role Storage**
- Roles stored in `user.role` column as: `ADMIN`, `EXPERT`, `AGRICOLE`, `FOURNISSEUR`
- Symfony automatically prefixes with `ROLE_` at runtime
- Registration allows: `ROLE_AGRICOLE`, `ROLE_EXPERT`, `ROLE_FOURNISSEUR` (public signup)
- `ROLE_ADMIN` can only be assigned by system administrators

---

## 🎯 EXPERT MODULE - CURRENT FEATURES

### **1. Dashboard** (`/expert/dashboard`)
**Controller**: `DashboardController::expert()`
**Template**: `templates/portal/expert/index.html.twig`

**Statistics Displayed**:
- Analyses completed this month
- Total analyses assigned to expert
- Total conseils (recommendations) created
- Pending analysis requests (unassigned)

---

### **2. Analysis Management** (`/expert/analyses`)

#### **A. List Analyses** (`ExpertAnalyseController::list()`)
**Route**: `/expert/analyses`
**Features**:
- View all analyses assigned to the logged-in expert
- Filter by:
  - Search term (in `resultat_technique`)
  - Diagnosis mode (vision/text)
  - Farm (`ferme`)
  - Date range (`date_from`, `date_to`)
- Ordered by date (newest first)

#### **B. View Analysis Details** (`ExpertAnalyseController::show()`)
**Route**: `/expert/analyse/{id}`
**Features**:
- View complete analysis details
- Display AI diagnosis results (if available)
- Show weather data for farm location
- List associated conseils (recommendations)
- Security: Only the assigned expert can view

#### **C. Pending Requests** (`ExpertAnalyseController::pendingRequests()`)
**Route**: `/expert/demandes-en-attente`
**Features**:
- View all analyses with status `en_attente` (waiting)
- Any expert can claim unassigned requests
- Shows: demandeur, farm, target plant/animal, description

#### **D. Take Request** (`ExpertAnalyseController::takeRequest()`)
**Route**: `/expert/demande/{id}/prendre-en-charge`
**Action**:
- Assigns the expert as `technicien` for the analysis
- Changes status from `en_attente` → `en_cours`
- Redirects to analysis detail page

#### **E. Create/Edit/Delete Analysis**
**Routes**:
- `/expert/analyse/new` - Create new analysis
- `/expert/analyse/{id}/edit` - Edit existing analysis
- `/expert/analyse/{id}/delete` - Delete analysis (POST with CSRF)

#### **F. Update Status** (`ExpertAnalyseController::updateStatus()`)
**Route**: `/expert/analyse/{id}/status/{status}` (POST)
**Valid Statuses**:
- `en_attente` - Waiting
- `en_cours` - In progress
- `terminee` - Completed
- `annulee` - Cancelled

#### **G. Export to PDF** (`ExpertAnalyseController::exportAnalysePdf()`)
**Route**: `/expert/analyse/{id}/export/pdf`
**Service**: `ReportService::generateAnalysePdf()`
**Output**: PDF report with analysis details

---

### **3. AI Diagnostic Features** (`ExpertAIController`)

#### **A. Vision Diagnostic (Image-based)** 
**Route**: `/expert/analyse/{id}/diagnose` (POST)
**Method**: `ExpertAIController::diagnose()`

**Process**:
1. Validates expert is assigned to analysis
2. Checks if analysis has an image (`image_url`)
3. Calls `GroqService::generateVisionDiagnostic()`
4. Fetches weather data for farm location
5. Stores AI results in `Analyse` entity:
   - `ai_diagnosis_result` (JSON)
   - `ai_confidence_score` (HIGH/MEDIUM/LOW)
   - `ai_diagnosis_date`
   - `weather_data` (JSON)

**AI Response Structure**:
```json
{
  "condition": "Disease/problem name",
  "confidence": "HIGH|MEDIUM|LOW",
  "symptoms": "Visual symptoms observed",
  "treatment": "Recommended treatment",
  "prevention": "Prevention measures",
  "urgency": "Immédiat|Dans la semaine|Surveiller",
  "needsExpertConsult": true|false,
  "rawResponse": "Full AI response"
}
```

#### **B. Text Diagnostic (Observation-based)**
**Route**: `/expert/analyse/{id}/diagnose-text` (GET/POST)
**Method**: `ExpertAIController::diagnoseText()`

**Process**:
1. Expert enters text observation/symptoms
2. Builds context data:
   - Farm name and location
   - Target plant/animal
   - Related plants/animals in farm
3. Calls `GroqService::generateTextDiagnostic()`
4. Stores results with `diagnosis_mode = 'text'`

#### **C. View AI Results**
**Route**: `/expert/analyse/{id}/ai-result`
**Template**: `templates/portal/expert/ai_result.html.twig`
**Displays**: Decoded JSON diagnosis results

#### **D. JSON API Endpoint**
**Route**: `/expert/analyse/{id}/diagnose/json` (POST)
**Returns**: JSON response for AJAX calls
**Use Case**: Frontend JavaScript integration

---

### **4. Conseil (Recommendations) Management** (`ExpertConseilController`)

#### **A. List Conseils** (`ExpertConseilController::list()`)
**Route**: `/expert/conseils`
**Features**:
- View all conseils for analyses assigned to expert
- Filter by:
  - Search term (in `description_conseil`)
  - Priority (`HAUTE`, `MOYENNE`, `BASSE`)
- Ordered by ID (newest first)

#### **B. View Conseil Details**
**Route**: `/expert/conseil/{id}`
**Security**: Only expert assigned to related analysis can view

#### **C. Create Conseil**
**Routes**:
- `/expert/conseil/new` - Standalone creation
- `/expert/analyse/{id}/conseil/new` - Create for specific analysis

#### **D. Edit/Delete Conseil**
**Routes**:
- `/expert/conseil/{id}/edit` - Edit existing conseil
- `/expert/conseil/{id}/delete` - Delete conseil (POST with CSRF)

---

## 🗄️ DATA MODEL

### **Analyse Entity** (`src/Entity/Analyse.php`)
```php
- id_analyse (PK)
- date_analyse (datetime)
- resultat_technique (text)
- id_technicien_id (FK → User) // The assigned expert
- id_ferme_id (FK → Ferme)
- id_demandeur (FK → User) // Who requested the analysis
- description_demande (text)
- id_animal_cible (FK → Animal, nullable)
- id_plante_cible (FK → Plante, nullable)
- image_url (string)
- statut (string: en_attente|en_cours|terminee|annulee)
- diagnosis_mode (string: vision|text)

// AI Fields
- ai_diagnosis_result (text, JSON)
- ai_diagnosis_date (datetime)
- ai_confidence_score (string: HIGH|MEDIUM|LOW)

// Weather Fields
- weather_data (json)
- weather_fetched_at (datetime)

// Relations
- conseils (OneToMany → Conseil)
```

### **Conseil Entity** (`src/Entity/Conseil.php`)
```php
- id_conseil (PK)
- description_conseil (text, min 10 chars)
- priorite (string: HAUTE|MOYENNE|BASSE)
- id_analyse (FK → Analyse)
```

### **User Entity** (`src/Entity/User.php`)
```php
- id_user (PK)
- nom, prenom, email, password
- cin (8 digits, unique)
- telephone (8 digits, unique)
- adresse, latitude, longitude
- role (string: ADMIN|EXPERT|AGRICOLE|FOURNISSEUR)
- image_url
- created_at, updated_at

// Relations
- fermes (OneToMany → Ferme)
- userFaces (OneToMany → UserFace) // Facial recognition
- userLogs (OneToMany → UserLog)
```

---

## 🔗 RELATIONS EXPLAINED

### **1. Expert ↔ Analyse Relation**
**Type**: Many-to-One (Expert can handle many analyses)
**Field**: `Analyse.technicien` → `User` (with ROLE_EXPERT)
**Purpose**: Assigns agricultural expert to analysis request

**Workflow**:
1. Farmer (ROLE_AGRICOLE) creates analysis request → status: `en_attente`
2. Expert views pending requests at `/expert/demandes-en-attente`
3. Expert claims request → becomes `technicien` → status: `en_cours`
4. Expert performs AI diagnosis (vision or text)
5. Expert creates conseils (recommendations)
6. Expert marks analysis as `terminee`

### **2. Expert ↔ Conseil Relation**
**Type**: Indirect (through Analyse)
**Logic**: Expert creates conseils for analyses they are assigned to
**Security**: `ConseilRepository::findByExpert()` filters by `analyse.technicien`

### **3. Admin ↔ Expert Relation**
**Type**: Role-based hierarchy
**Admin Capabilities**:
- View all analyses (not just assigned ones)
- Manage all users (including experts)
- Access admin dashboard (`/admin/dashboard`)
- View statistics across all experts

**Admin Routes**:
- `/admin/analyses` - View all analyses
- `/admin/conseils` - View all conseils
- `/admin/users` - Manage users (including experts)
- `/admin/statistics` - System-wide statistics

### **4. Agriculteur (Farmer) ↔ Expert Relation**
**Type**: Request-based interaction
**Workflow**:
1. Farmer creates analysis request (demandeur)
2. Expert claims and processes request (technicien)
3. Expert provides diagnosis and conseils
4. Farmer views results in their dashboard

**Farmer Routes**:
- `/agricole/dashboard` - View their farms, plants, animals
- `/agricole/exploitation` - Manage exploitation
- Can view analyses they requested

---

## 🤖 AI SERVICES ARCHITECTURE

### **GroqService** (`src/Service/GroqService.php`)

#### **Methods**:

1. **`generateVisionDiagnostic(string $imageUrl, array $contextData)`**
   - Uses: `llama-3.2-11b-vision-preview`
   - Input: Image URL + farm context
   - Output: `DiagnosisResult` DTO
   - Prompt: Analyzes image for plant/animal diseases

2. **`generateTextDiagnostic(string $observation, array $contextData)`**
   - Uses: Same vision model (can process text)
   - Input: Text observation + farm context
   - Output: `DiagnosisResult` DTO
   - Prompt: Analyzes text symptoms

3. **`generateExecutiveSummary(...)`**
   - Uses: `llama-3.3-70b-versatile`
   - Purpose: Generate farm report summaries
   - Input: Farm stats (analyses, conseils, priorities)

#### **Context Data Structure**:
```php
[
    'ferme' => [
        'nom' => 'Farm name',
        'lieu' => 'Location'
    ],
    'plantes' => [
        ['nom' => 'Plant name', 'type' => 'Soil type'],
        // ... up to 5 plants
    ],
    'animaux' => [
        ['espece' => 'Species', 'race' => 'Breed', 'etat' => 'Health status'],
        // ... up to 5 animals
    ],
    'analyseCible' => [
        'nom' => 'Target name',
        'type' => 'plante|animal'
    ]
]
```

### **GroqChatService** (`src/Service/GroqChatService.php`)
- **Purpose**: General chatbot for FarmAI platform
- **Model**: `llama-3.3-70b-versatile`
- **Features**:
  - Session-based conversation history
  - Automatic retry on transient errors (502/503/504)
  - Max 10 message history
  - System prompt: FarmAI assistant for account/platform help

### **WeatherService** (`src/Service/WeatherService.php`)
- **API**: OpenWeather API
- **Purpose**: Fetch weather data for farm locations
- **Usage**: Contextual data for AI diagnostics

---

## 🧪 TESTING INFRASTRUCTURE

### **Test Structure**:
- **Unit Tests**: Entity validation, service logic
- **Functional Tests**: Controller endpoints, form submissions
- **Staging Tests**: AI integration, Python API handshake

### **Key Test Files**:
1. **`tests/Functional/Controller/ExpertAnalyseControllerTest.php`**
   - Tests all expert analyse routes
   - Validates CRUD operations
   - Tests PDF generation

2. **`tests/Functional/Controller/ExpertConseilControllerTest.php`**
   - Tests conseil management
   - Validates security (expert ownership)

3. **`tests/Staging/ExpertAIConnectionTest.php`**
   - Tests Groq API integration
   - Validates AI diagnostic responses

4. **`tests/Staging/ExpertModuleHandshakeTest.php`**
   - Tests button→action→response chains
   - Validates expert workflow

5. **`tests/Staging/ExpertButtonConnectionTest.php`**
   - Tests UI interactions without browser automation

### **Running Tests**:
```bash
# All tests
php bin/phpunit

# Staging tests only (requires Python API on port 5000)
php bin/phpunit tests/Staging/

# Single test
php bin/phpunit tests/Staging/ExpertAIConnectionTest.php
```

---

## 🚀 DEPLOYMENT & SETUP

### **1. Install Dependencies**
```bash
composer install
```

### **2. Configure Environment**
Copy `.env.example` to `.env` and set:
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/farmai?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
GROQ_API_KEY=your_groq_api_key_here
GROQ_MODEL=llama-3.2-11b-vision-preview
OPENWEATHER_API_KEY=your_openweather_key_here
PYTHON_API_URL=http://127.0.0.1:5000
```

### **3. Database Setup**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load  # If fixtures exist
```

### **4. Start Python API** (for facial recognition)
```bash
cd python_api
source venv/bin/activate
python app.py  # Runs on port 5000
```

### **5. Run Symfony Server**
```bash
php -S localhost:8000 -t public public/router.php
```

### **6. Access Expert Module**
1. Register as expert: `http://localhost:8000/register` (select "Expert" role)
2. Login: `http://localhost:8000/login`
3. Dashboard: `http://localhost:8000/expert/dashboard`

---

## 📁 FILE STRUCTURE

```
src/
├── Controller/
│   ├── Admin/              # Admin-only controllers
│   ├── Web/
│   │   ├── ExpertAIController.php       # AI diagnostics
│   │   ├── ExpertAnalyseController.php  # Analysis CRUD
│   │   ├── ExpertConseilController.php  # Conseil CRUD
│   │   └── DashboardController.php      # Role-based dashboards
│   └── ...
├── Entity/
│   ├── Analyse.php         # Analysis entity
│   ├── Conseil.php         # Recommendation entity
│   ├── User.php            # User/Expert entity
│   ├── Ferme.php           # Farm entity
│   ├── Plante.php          # Plant entity
│   └── Animal.php          # Animal entity
├── Repository/
│   ├── AnalyseRepository.php
│   ├── ConseilRepository.php
│   └── ...
├── Service/
│   ├── GroqService.php              # AI diagnostics
│   ├── GroqChatService.php          # Chatbot
│   ├── WeatherService.php           # Weather API
│   ├── ReportService.php            # PDF generation
│   └── PythonFaceRecognitionService.php
├── Form/
│   ├── AnalyseType.php
│   ├── ConseilType.php
│   └── ...
└── DTO/
    └── DiagnosisResult.php  # AI response DTO

templates/
└── portal/
    └── expert/
        ├── index.html.twig           # Dashboard
        ├── analyses.html.twig        # Analysis list
        ├── analyse_show.html.twig    # Analysis details
        ├── analyse_new.html.twig     # Create analysis
        ├── analyse_edit.html.twig    # Edit analysis
        ├── pending_requests.html.twig # Pending requests
        ├── conseils.html.twig        # Conseil list
        ├── conseil_show.html.twig    # Conseil details
        ├── conseil_new.html.twig     # Create conseil
        ├── conseil_edit.html.twig    # Edit conseil
        ├── ai_result.html.twig       # AI diagnosis results
        ├── diagnose_text.html.twig   # Text diagnostic form
        └── diagnose_unified.html.twig # Unified diagnostic UI

tests/
├── Functional/
│   └── Controller/
│       ├── ExpertAnalyseControllerTest.php
│       └── ExpertConseilControllerTest.php
└── Staging/
    ├── ExpertAIConnectionTest.php
    ├── ExpertModuleHandshakeTest.php
    └── ExpertButtonConnectionTest.php
```

---

## 🔐 SECURITY FEATURES

### **1. Role-Based Access Control**
- All expert routes protected with `#[IsGranted('ROLE_EXPERT')]`
- Experts can only view/edit analyses they are assigned to
- CSRF protection on all POST/DELETE operations

### **2. Ownership Validation**
```php
// Example from ExpertAnalyseController
if ($analyse->getTechnicien() !== $this->getUser()) {
    throw $this->createAccessDeniedException('...');
}
```

### **3. Input Validation**
- Symfony Form validation
- Doctrine constraints (`@Assert\NotBlank`, `@Assert\Length`, etc.)
- Status validation (only valid statuses allowed)

### **4. API Security**
- Groq API key stored in `.env` (never committed)
- HTTP client timeout protection (30s)
- Retry logic for transient failures

---

## 📊 STATISTICS & REPORTING

### **Expert Dashboard Stats**:
- `analysesThisMonth`: Count of analyses this month
- `analysesTotal`: Total analyses assigned to expert
- `conseilsTotal`: Total conseils created by expert
- `pendingRequests`: Unassigned analysis requests

### **Repository Methods**:
```php
// AnalyseRepository
countByTechnicien(int $technicienId): int
countByTechnicienThisMonth(int $technicienId): int
findPendingRequests(): array
countPendingRequests(): int

// ConseilRepository
countByTechnicien(int $technicienId): int
countByTechnicienAndPriorite(int $technicienId, string $priorite): int
findByExpert(int $technicienId, ?string $search, ?string $priorite): array
```

---

## 🎨 FRONTEND TEMPLATES

### **Expert Dashboard** (`portal/expert/index.html.twig`)
- Statistics cards
- Quick actions (view pending requests, create analysis)
- Recent analyses list

### **Analysis List** (`portal/expert/analyses.html.twig`)
- Filterable table (search, mode, farm, date range)
- Status badges
- AI diagnosis indicators
- Action buttons (view, edit, delete)

### **Analysis Details** (`portal/expert/analyse_show.html.twig`)
- Full analysis information
- AI diagnosis results (if available)
- Weather data display
- Associated conseils list
- Action buttons (diagnose, add conseil, export PDF)

### **AI Diagnostic Forms**
- **Vision**: Upload image → AI analyzes → Results
- **Text**: Enter observations → AI analyzes → Results

---

## 🔄 WORKFLOW DIAGRAMS

### **Expert Analysis Workflow**:
```
1. Farmer creates analysis request
   ↓
2. Status: en_attente (pending)
   ↓
3. Expert views pending requests
   ↓
4. Expert claims request (becomes technicien)
   ↓
5. Status: en_cours (in progress)
   ↓
6. Expert performs AI diagnosis (vision or text)
   ↓
7. AI stores results in analyse.ai_diagnosis_result
   ↓
8. Expert creates conseils (recommendations)
   ↓
9. Expert marks analysis as terminee (completed)
   ↓
10. Farmer views results in their dashboard
```

### **AI Diagnostic Workflow**:
```
1. Expert opens analysis
   ↓
2. Clicks "Diagnose" button
   ↓
3. System checks if image exists (vision) or prompts for text
   ↓
4. Fetches weather data for farm location
   ↓
5. Builds context data (farm, plants, animals)
   ↓
6. Calls GroqService with image/text + context
   ↓
7. Groq API returns structured diagnosis
   ↓
8. System stores results in database
   ↓
9. Expert views AI results
   ↓
10. Expert creates conseils based on diagnosis
```

---

## 🐛 KNOWN ISSUES & LIMITATIONS

### **1. Python API Dependency**
- Facial recognition requires Python Flask API on port 5000
- Must be started manually before running staging tests
- No automatic startup/health check

### **2. Database Configuration Mismatch**
- `compose.yaml` defines PostgreSQL
- `.env` uses MySQL/MariaDB
- **Action Required**: Align DB configuration

### **3. AI Model Limitations**
- Vision model requires publicly accessible image URLs
- No local image processing
- Confidence scores are AI-generated (not validated)

### **4. No Real-time Notifications**
- Experts must manually check pending requests
- No push notifications when new requests arrive

### **5. Single Expert Assignment**
- Each analysis can only have one expert (technicien)
- No collaboration features between experts

---

## 🚀 FUTURE ENHANCEMENTS

### **Potential Features**:
1. **Real-time Notifications**: WebSocket/Mercure for instant updates
2. **Multi-expert Collaboration**: Allow multiple experts per analysis
3. **AI Model Selection**: Let experts choose different AI models
4. **Batch Processing**: Diagnose multiple analyses at once
5. **Mobile App**: Native iOS/Android app for field work
6. **Offline Mode**: Cache analyses for offline diagnosis
7. **Expert Ratings**: Farmers rate expert performance
8. **Automated Assignment**: AI-based expert assignment based on expertise
9. **Video Diagnostics**: Support video uploads for better analysis
10. **Integration with IoT**: Connect farm sensors for real-time data

---

## 📞 SUPPORT & DOCUMENTATION

### **Key Configuration Files**:
- `.env` - Environment variables
- `config/packages/doctrine.yaml` - Database configuration
- `config/packages/security.yaml` - Security/authentication
- `composer.json` - PHP dependencies

### **Useful Commands**:
```bash
# Clear cache
php bin/console cache:clear

# Database migrations
php bin/console doctrine:migrations:migrate

# Create new migration
php bin/console make:migration

# Run tests
php bin/phpunit

# Check code style
vendor/bin/phpstan analyse src
```

---

## 📝 CONCLUSION

The **Expert Module** is a fully functional AI-powered agricultural diagnostic system that enables experts to:
- Claim and manage analysis requests from farmers
- Perform AI-based diagnostics (vision + text)
- Create recommendations (conseils) with priority levels
- Export reports to PDF
- Track statistics and performance

**Key Strengths**:
✅ Modern Symfony 6.4 architecture
✅ AI integration with Groq (LLaMA models)
✅ Comprehensive security (RBAC, CSRF, ownership validation)
✅ Well-tested (functional + staging tests)
✅ Clean separation of concerns (Controller/Service/Repository)

**Areas for Improvement**:
⚠️ Database configuration alignment (PostgreSQL vs MySQL)
⚠️ Python API dependency management
⚠️ Real-time notification system
⚠️ Multi-expert collaboration features

---

**Generated**: May 6, 2026
**Version**: 1.0
**Author**: Kiro AI Assistant
