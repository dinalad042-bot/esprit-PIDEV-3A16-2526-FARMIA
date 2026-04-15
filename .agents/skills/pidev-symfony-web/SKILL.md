---
name: pidev-symfony-web
description: >
  Use this skill for ANY task related to the PIDEV 3A Web Sprint project using Symfony 6.4.
  Trigger whenever the user mentions: Symfony, Twig, Doctrine, MVC, PIDEV, sprint web,
  entities, controllers, templates, forms, validation, reverse engineering, FrontOffice,
  BackOffice, bundles, routing, assets, base.html.twig, or any Symfony console command.
  This skill enforces ALL technical constraints from ESPRIT's PIDEV 3A 2025-2026 requirements
  and guides development decisions accordingly. Always activate this skill even for seemingly
  simple Symfony questions — the constraints are strict and violations cost grades.
---

# PIDEV Symfony 6.4 — Web Sprint Skill

## Context

This is ESPRIT's **PIDEV 3A 2025-2026**, Sprint 2 (Web). The project is a **multiplatform app**
with two clients: Java (Desktop) and Web (Symfony 6.4), sharing **one MySQL database**.

The web sprint is evaluated through **individual follow-up sessions (suivis)** and a **team
integration validation**. No exam. Not retakeable.

---

## ⛔ Hard Constraints — Never Violate These

| Constraint | Rule |
|---|---|
| **No FOSUserBundle** | Never use it for user management in the web part |
| **No AdminBundle** | Backoffice must be custom-built — no EasyAdmin, SonataAdmin, etc. |
| **Images** | Always store as **URL** (string), never as blob/binary |
| **DB** | One shared database between Java and Web clients |
| **Modules** | Each module needs ≥ 2 entities + at least 1 relation between them |
| **GitHub** | Project must be on GitHub; no GitHub = no grade recovery |
| **Both sides** | Every sprint must implement both **FrontOffice** AND **BackOffice** |
| **No bundle shortcuts** | Business logic must be hand-coded, not delegated to admin bundles |

---

## Architecture — MVC Pattern

```
src/
├── Controller/          # Handles HTTP requests, calls services, returns responses
├── Entity/              # Doctrine entities (annotated PHP classes = DB tables)
├── Repository/          # Custom Doctrine queries per entity
├── Form/                # Symfony Form types (StudentType, etc.)
└── Service/             # Business logic layer (optional but clean)

templates/
├── base.html.twig       # Main layout (CSS, JS blocks)
├── home/
│   └── index.html.twig  # Extends base, redefines {% block body %}
└── admin/
    ├── base_admin.html.twig   # Admin layout, extends base.html.twig
    ├── Partials/
    │   ├── sidebar.html.twig
    │   └── navbar.html.twig
    └── add-user.html.twig     # Extends base_admin, redefines {% block admin_content %}
```

---

## Twig Template Integration

### Core rules
- `base.html.twig` holds the master layout: CSS in `{% block stylesheets %}`, JS in `{% block javascripts %}`
- Always use `{{ asset('path/to/file.css') }}` — never hardcoded relative paths
- Child templates inherit with `{% extends 'base.html.twig' %}` and override blocks
- Partial components (sidebar, navbar) are included via `{% include 'admin/Partials/sidebar.html.twig' %}`

### FrontOffice layout pattern
```twig
{# templates/home/index.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Home{% endblock %}

{% block body %}
  {# page-specific content here #}
{% endblock %}
```

### BackOffice layout pattern
```twig
{# templates/admin/base_admin.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
  {% include 'admin/Partials/sidebar.html.twig' %}
  {% include 'admin/Partials/navbar.html.twig' %}

  <div class="main-content">
    {% block admin_content %}{% endblock %}
  </div>
{% endblock %}
```

```twig
{# templates/admin/add-user.html.twig #}
{% extends 'admin/base_admin.html.twig' %}

{% block admin_content %}
  {# specific admin page content #}
{% endblock %}
```

---

## Doctrine Entities

### Minimum requirement
- Each module: **≥ 2 entities**, **≥ 1 relation** (ManyToOne, OneToMany, ManyToMany, OneToOne)
- Image fields must be declared as `string` (URL), not binary

### Entity example with relation
```php
#[ORM\Entity(repositoryClass: EtudiantRepository::class)]
class Etudiant {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageUrl = null;  // URL, never blob

    #[ORM\ManyToOne(inversedBy: 'etudiants')]
    private ?Classe $classe = null;
}
```

### Reverse Engineering (from existing DB)
When the DB already exists:
```bash
# Method 1 — Custom command (workshop method)
symfony console make:command app:generate:entities
# → implement logic in src/Command/GenerateEntitiesCommand.php
symfony console app:generate:entities

# Method 2 — Script-based
php reverse-engineer.php   # place script at project root
php bin/console make:entity --regenerate

# Then sync migrations
symfony console doctrine:migrations:diff
symfony console doctrine:migrations:migrate
```

---

## Forms & Server-Side Validation

### Never rely on HTML5 validation alone — always validate server-side

```php
// src/Form/StudentType.php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder->add('nsc')->add('email');
    }
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults(['data_class' => Student::class]);
    }
}
```

### Assert constraints on entity
```php
use Symfony\Component\Validator\Constraints as Assert;

class Student {
    #[Assert\NotBlank]
    private ?string $nsc = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[Assert\Length(min: 10, minMessage: "Min {{ limit }} characters required")]
    private ?string $password = null;

    #[Assert\Positive]
    private ?int $age = null;
}
```

### Controller form handling
```php
public function add(Request $request): Response {
    $student = new Student();
    $form = $this->createForm(StudentType::class, $student);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->em->persist($student);
        $this->em->flush();
        return $this->redirectToRoute('student_list');
    }

    return $this->render('student/add.html.twig', ['form' => $form->createView()]);
}
```

### Twig form rendering with custom error display
```twig
{# Disable HTML5 validation to show server-side errors #}
{{ form_start(form, {'attr': {'novalidate': 'novalidate'}}) }}

{{ form_label(form.nsc, "Numéro d'inscription") }}
{{ form_errors(form.nsc) }}
{{ form_widget(form.nsc) }}

{{ form_label(form.email, "Email") }}
{{ form_errors(form.email) }}
{{ form_widget(form.email) }}

<button type="submit">Submit</button>
{{ form_end(form) }}
```

### Twig form helpers reference
| Helper | Purpose |
|---|---|
| `form_start(form, options)` | `<form>` tag; add `{'attr':{'novalidate':...}}` to disable HTML5 |
| `form_label(form.field, 'Label')` | Renders `<label>` |
| `form_widget(form.field)` | Renders `<input>` or `<select>` |
| `form_errors(form.field)` | Renders validation error messages |
| `form_row(form.field)` | label + errors + widget combined |
| `form_rest(form)` | Renders all remaining fields |
| `form_end(form)` | `</form>` tag |

---

## Routing

```php
// Annotation-based routing (Symfony 6.x default)
#[Route('/student', name: 'student_list')]
public function list(): Response { ... }

#[Route('/student/add', name: 'student_add', methods: ['GET', 'POST'])]
public function add(Request $request): Response { ... }

#[Route('/student/{id}', name: 'student_show')]
public function show(int $id): Response { ... }
```

---

## Useful Symfony Console Commands

```bash
# Create project
symfony new MyProject --version="6.4"

# Make controller (auto-creates template folder)
symfony console make:controller HomeController

# Make entity
symfony console make:entity Student

# Make form type
symfony console make:form StudentType

# Database
symfony console doctrine:database:create
symfony console make:migration
symfony console doctrine:migrations:migrate

# Reverse engineering
symfony console doctrine:migrations:diff   # compare DB state vs entities
```

---

## Common Mistakes to Avoid

1. **Using FOSUserBundle** → instant constraint violation
2. **Using EasyAdmin/SonataAdmin** → AdminBundle is forbidden
3. **Storing images as blob** → must be URL string
4. **Single entity per module** → need ≥ 2 + 1 relation
5. **No `novalidate`** → server errors won't show in form
6. **Hardcoded asset paths** → use `{{ asset('...') }}` always
7. **No GitHub commits** → no grade recovery possible
8. **Missing FrontOffice or BackOffice** → both are required per sprint

---

## Evaluation Touchpoints (Web Sprint)

| Session | What's graded |
|---|---|
| Séance 8 | Project on GIT, template integration (FO + BO), DB created |
| Séance 9 | CRUD + input validation + basic business logic |
| Séance 10 | Advanced business logic + external APIs/bundles + AI integration |
| Séance 11–13 | Performance, tests, Java↔Web integration |
| Séance 14 | Final integration defense |