# 🌿 FarmAI — Expert Module Demo
## Student: Alaeddin | PIDEV 3A 2025-2026

## DEMO ORDER (follow this sequence for evaluator)

### ① GitHub First (30 seconds)
URL: https://github.com/dinalad042-bot/esprit-PIDEV-3A16-2526-FARMIA/tree/Alaeddin-expertise-branch
Show: commits history, file structure

### ② BackOffice — Analyse List
URL: http://localhost:8000/admin/analyse
Show: list with search bar, stats cards (total/this month/no conseils)
Show: data loaded from shared Java DB

### ③ BackOffice — Create Analyse (Validation Demo)
URL: http://localhost:8000/admin/analyse/new
Do: submit empty form → show server-side errors
Do: fill form → submit → show success flash message

### ④ AI Diagnostic (Séance 10 ⭐)
URL: http://localhost:8000/admin/analyse/1/ai-diagnostic
Type: "Les feuilles présentent des taches jaunes avec flétrissement progressif"
Click: Analyser avec IA
Show: condition, confidence badge, symptoms, treatment, prevention
Click: Sauvegarder → redirects to show page

### ⑤ PDF Export (Séance 10 ⭐)
URL: http://localhost:8000/admin/report/analyse/1/pdf
Show: professional PDF opens in browser with all data

### ⑥ Weather API (Séance 10 ⭐)
URL: http://localhost:8000/admin/report/analyse/1/weather
Show: live weather for farm location
Show: agricultural advice (temperature/humidity/wind)

### ⑦ Conseil Management
URL: http://localhost:8000/admin/conseil
Show: priority filter (HAUTE/MOYENNE/BASSE)
Show: colored priority badges 🔴🟡🟢
Show: stats cards per priority

### ⑧ FrontOffice (Different from BO)
URL: http://localhost:8000/analyse
Show: card grid layout (different from admin table)
URL: http://localhost:8000/conseil
Show: priority-colored cards

### ⑨ Expert Dashboard
URL: http://localhost:8000/expert/dashboard
Show: welcome banner, 4 stat cards
Show: recent analyses list
Show: priority breakdown bars

## PIDEV Compliance Summary
✅ 2 entities: Analyse + Conseil (1:N relation)
✅ Images: stored as URL strings (not blob)
✅ Validation: server-side Assert constraints + novalidate
✅ FrontOffice: /analyse /conseil (card views)
✅ BackOffice: /admin/analyse /admin/conseil (table views)
✅ AI: Groq LLM text diagnostic + vision
✅ External API: OpenWeatherMap
✅ PDF: DomPDF professional reports
✅ No FOSUserBundle
✅ No AdminBundle
✅ GitHub: 4+ commits with clear messages
