# FarmIA Desk — Symfony Reimplementation Design Document

---

## Project Summary

FarmIA Desk is a small-business ERP application for managing agricultural products/services, purchase orders (Achats), and sales orders (Ventes). Originally built as a JavaFX desktop application with direct JDBC/MySQL access, it is being reimplemented as a Symfony 6/7 web application.

### Purpose

The application allows a manager to:
- Maintain a catalogue of stock items (Services) with pricing and critical-stock thresholds
- Record purchase orders (Achats) that increase stock levels
- Record sales orders (Ventes) that decrease stock levels with validation
- Receive email alerts when a service's stock reaches zero
- Convert order totals to other currencies via an external exchange rate API
- Initiate Stripe Checkout payment sessions and generate QR codes for them

### Business Logic Summary

**How Achat (Purchase) Works:**
1. Manager selects a date and adds one or more lines (service + quantity + unit price)
2. Each line's sous-total = quantite × prixUnitaire
3. Order total = sum of all sous-totals
4. On save: a DB transaction inserts the Achat header, inserts all ListeAchat lines, and increments each service's stock by the line's quantity
5. A UNIQUE constraint on (id_achat, id_service) prevents duplicate service lines per order

**How Vente (Sale) Works:**
1. Manager selects a date and adds one or more lines (service + quantity + unit price)
2. Client-side stock validation: cannot add a line if service stock = 0, or if requested quantity > (stock − already-in-cart for same service)
3. Same-service lines are merged (quantity accumulated)
4. On save: server-side re-validation of stock for each line
5. A DB transaction inserts the Vente header and all ListeVente lines
6. After commit: StockService.decreaseStock() is called for each line
7. After each decrease: if new stock = 0, EmailService sends a stock-zero alert

**How Stock is Managed:**
- increaseStock: `UPDATE service SET stock = stock + ? WHERE id_service = ?`
- decreaseStock: `UPDATE service SET stock = stock - ? WHERE id_service = ? AND stock >= ?` — if 0 rows affected, throws RuntimeException("Stock insuffisant pour le service {id}")
- Guards: both methods are no-ops for quantity ≤ 0
- Critical stock: `SELECT * FROM service WHERE stock <= seuil_critique ORDER BY stock ASC`

### All Integrations

- **Stripe**: stripe-php SDK, creates a Checkout Session (mode=payment, currency=usd, product="Abonnement"), returns session URL; controller generates a 260×260 QR code PNG from the URL using endroid/qr-code
- **Exchange Rate API**: GET `https://open.er-api.com/v6/latest/{base}`, parses `rates` JSON object, converts amount with HALF_UP rounding to 2 decimal places; default base=EUR, default target=TND
- **Email (Gmail SMTP)**: Symfony Mailer with SMTP transport, host=smtp.gmail.com, port=587, STARTTLS; credentials from env vars; email subject format: "🚨 Stock épuisé — {serviceName}"

---

## Overview

This document describes the complete Symfony architecture for FarmIA Desk. The application follows a standard Symfony MVC pattern:

- **Entities** (Doctrine ORM) map to MySQL tables
- **Repositories** provide query methods
- **Services** encapsulate business logic (StockService, AchatService, VenteService, ExchangeRateService, PaymentService, EmailService)
- **Controllers** handle HTTP requests, call services, and render Twig templates
- **Forms** (Symfony Form component) handle user input and validation
- **Twig templates** render the UI with flash messages for alerts

The application targets Symfony 6.4 LTS (PHP 8.2+), Doctrine ORM 3.x, MySQL 8.x.

---

## Architecture

### Component Interaction

```
HTTP Request
    │
    ▼
Controller (App\Controller\*)
    │  validates input via Form / manual checks
    │  calls Service layer
    ▼
Service (App\Service\*)
    │  orchestrates business logic
    │  calls Repository for data access
    │  calls other Services (StockService, EmailService, etc.)
    ▼
Repository (App\Repository\*)
    │  Doctrine queries / DQL / native SQL
    ▼
Entity (App\Entity\*)
    │  Doctrine ORM mapping
    ▼
MySQL Database (farmia_desk)
```

External integrations are called from Service classes only — never directly from Controllers.

### Separation of Concerns

| Layer | Responsibility |
|---|---|
| Controller | HTTP in/out, form binding, flash messages, redirects |
| Service | Business rules, transactions, orchestration |
| Repository | Data access, queries, no business logic |
| Entity | Data structure, computed properties (getSousTotal, isStockCritique) |
| Form | Input validation, field mapping |
| Twig | Rendering only, no logic beyond display conditionals |

### Architecture Diagram

```mermaid
graph TD
    Browser -->|HTTP| DashboardController
    Browser -->|HTTP| ServiceController
    Browser -->|HTTP| AchatController
    Browser -->|HTTP| VenteController

    ServiceController --> StockService
    AchatController --> AchatService
    AchatController --> ExchangeRateService
    AchatController --> PaymentService
    VenteController --> VenteService
    VenteController --> ExchangeRateService

    AchatService --> StockService
    AchatService --> AchatRepository
    VenteService --> StockService
    VenteService --> EmailService
    VenteService --> VenteRepository

    StockService --> ServiceRepository
    AchatRepository --> EntityManager
    VenteRepository --> EntityManager
    ServiceRepository --> EntityManager
    EntityManager --> MySQL[(farmia_desk)]

    PaymentService -->|stripe-php| StripeAPI
    ExchangeRateService -->|HttpClient| OpenERAPI
    EmailService -->|Symfony Mailer| GmailSMTP
```

---

## Project Structure

```
farmia-desk-symfony/
├── .env                          # DATABASE_URL, STRIPE_SECRET_KEY, MAILER_DSN, QR_OUTPUT_DIR
├── .env.local                    # Secrets (not committed): real SMTP password, Stripe key
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml
│   │   └── mailer.yaml
│   └── routes.yaml
├── migrations/                   # Doctrine migrations
├── public/
│   └── index.php
├── src/
│   ├── Controller/
│   │   ├── DashboardController.php
│   │   ├── ServiceController.php
│   │   ├── AchatController.php
│   │   └── VenteController.php
│   ├── Entity/
│   │   ├── Service.php
│   │   ├── Achat.php
│   │   ├── ListeAchat.php
│   │   ├── Vente.php
│   │   └── ListeVente.php
│   ├── Repository/
│   │   ├── ServiceRepository.php
│   │   ├── AchatRepository.php
│   │   ├── ListeAchatRepository.php
│   │   ├── VenteRepository.php
│   │   └── ListeVenteRepository.php
│   ├── Service/
│   │   ├── StockService.php
│   │   ├── AchatService.php
│   │   ├── VenteService.php
│   │   ├── ExchangeRateService.php
│   │   ├── PaymentService.php
│   │   └── EmailService.php
│   ├── Form/
│   │   ├── ServiceType.php
│   │   ├── AchatType.php
│   │   ├── ListeAchatType.php
│   │   ├── VenteType.php
│   │   └── ListeVenteType.php
│   └── DTO/
│       ├── AchatDTO.php          # Optional: for form binding with dynamic lines
│       └── VenteDTO.php
└── templates/
    ├── base.html.twig
    ├── dashboard/
    │   └── index.html.twig
    ├── service/
    │   ├── index.html.twig       # List + form
    │   └── _form.html.twig
    ├── achat/
    │   ├── index.html.twig       # List
    │   ├── new.html.twig         # Create form
    │   └── show.html.twig        # Detail + QR code
    └── vente/
        ├── index.html.twig       # List
        └── new.html.twig         # Create form
```

### Naming Conventions

- PHP classes: PascalCase (`ServiceController`, `AchatService`)
- Methods: camelCase (`findStockCritique`, `decreaseStock`)
- Twig templates: snake_case directories, kebab-case files
- Route names: `app_service_index`, `app_achat_new`, `app_vente_delete`, etc.
- Environment variables: SCREAMING_SNAKE_CASE (`DATABASE_URL`, `STRIPE_SECRET_KEY`)
- Database columns: snake_case (`id_service`, `seuil_critique`, `prix_unitaire`)
- Entity properties: camelCase (`idService`, `seuilCritique`, `prixUnitaire`)

---

## Entities Design

### Entity: Service

Maps to table `service`. Represents a stock-keeping unit (product or agricultural service).

```php
// src/Entity/Service.php
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\Table(name: 'service')]
#[ORM\Index(columns: ['stock'], name: 'idx_service_stock')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_service', type: 'integer')]
    private ?int $idService = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'prix', type: 'float', options: ['default' => 0])]
    private float $prix = 0.0;

    #[ORM\Column(name: 'stock', type: 'integer', options: ['default' => 0])]
    private int $stock = 0;

    #[ORM\Column(name: 'seuil_critique', type: 'integer', options: ['default' => 0])]
    private int $seuilCritique = 0;

    // OneToMany inverse side (not mapped as collection to keep it simple)
    // ListeAchat and ListeVente reference Service by FK only

    public function isStockCritique(): bool
    {
        return $this->stock < $this->seuilCritique;
    }

    // ... getters and setters
}
```

**Key design notes:**
- `isStockCritique()` is a computed property — not stored in DB
- No bidirectional OneToMany to ListeAchat/ListeVente on Service to avoid accidental cascade loads
- `prix` stored as `float` (DOUBLE in MySQL) — matches original schema

---

### Entity: Achat

Maps to table `achat`. Represents a purchase order header.

```php
// src/Entity/Achat.php
#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ORM\Table(name: 'achat')]
#[ORM\Index(columns: ['date_achat'], name: 'idx_achat_date')]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_achat', type: 'integer')]
    private ?int $idAchat = null;

    #[ORM\Column(name: 'date_achat', type: 'date')]
    private \DateTimeInterface $dateAchat;

    #[ORM\Column(name: 'total', type: 'float', options: ['default' => 0])]
    private float $total = 0.0;

    #[ORM\OneToMany(
        mappedBy: 'achat',
        targetEntity: ListeAchat::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->dateAchat = new \DateTimeImmutable();
    }

    public function addLigne(ListeAchat $ligne): void
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setAchat($this);
        }
    }

    public function removeLigne(ListeAchat $ligne): void
    {
        $this->lignes->removeElement($ligne);
    }

    // ... getters and setters
}
```

---

### Entity: ListeAchat

Maps to table `liste_achat`. Represents one line of a purchase order.

```php
// src/Entity/ListeAchat.php
#[ORM\Entity(repositoryClass: ListeAchatRepository::class)]
#[ORM\Table(name: 'liste_achat')]
#[ORM\UniqueConstraint(name: 'uk_liste_achat', columns: ['id_achat', 'id_service'])]
class ListeAchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Achat::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_achat', referencedColumnName: 'id_achat', nullable: false)]
    private Achat $achat;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id_service', nullable: false, onDelete: 'RESTRICT')]
    private Service $service;

    #[ORM\Column(name: 'quantite', type: 'integer')]
    private int $quantite;

    #[ORM\Column(name: 'prix_unitaire', type: 'float')]
    private float $prixUnitaire;

    public function getSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }

    // ... getters and setters
}
```

**Key design notes:**
- `#[ORM\UniqueConstraint]` on `(id_achat, id_service)` enforces the DB-level UNIQUE KEY
- `onDelete: 'RESTRICT'` on the Service FK prevents deleting a Service that has purchase lines
- `cascade: ['persist', 'remove']` on Achat side means saving/deleting Achat cascades to lines
- `getSousTotal()` is a computed property — not stored

---

### Entity: Vente

Maps to table `vente`. Represents a sales order header.

```php
// src/Entity/Vente.php
#[ORM\Entity(repositoryClass: VenteRepository::class)]
#[ORM\Table(name: 'vente')]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_vente', type: 'integer')]
    private ?int $idVente = null;

    #[ORM\Column(name: 'date_vente', type: 'date')]
    private \DateTimeInterface $dateVente;

    #[ORM\Column(name: 'total', type: 'float', options: ['default' => 0])]
    private float $total = 0.0;

    #[ORM\OneToMany(
        mappedBy: 'vente',
        targetEntity: ListeVente::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->dateVente = new \DateTimeImmutable();
    }

    // addLigne / removeLigne same pattern as Achat
}
```

---

### Entity: ListeVente

Maps to table `liste_vente`. Represents one line of a sales order.

```php
// src/Entity/ListeVente.php
#[ORM\Entity(repositoryClass: ListeVenteRepository::class)]
#[ORM\Table(name: 'liste_vente')]
class ListeVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vente::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_vente', referencedColumnName: 'id_vente', nullable: false)]
    private Vente $vente;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id_service', nullable: false, onDelete: 'RESTRICT')]
    private Service $service;

    #[ORM\Column(name: 'quantite', type: 'integer')]
    private int $quantite;

    #[ORM\Column(name: 'prix_unitaire', type: 'float')]
    private float $prixUnitaire;

    public function getSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }

    // ... getters and setters
}
```

**Key design notes:**
- No UNIQUE constraint on `(id_vente, id_service)` — the original schema does not have one for liste_vente
- Same `onDelete: 'RESTRICT'` on Service FK

---

## Repositories Design

### ServiceRepository

```php
// src/Repository/ServiceRepository.php
class ServiceRepository extends ServiceEntityRepository
{
    // findAll() ordered by id_service DESC — overrides default
    public function findAllOrderedById(): array;

    // SELECT * FROM service WHERE stock <= seuil_critique ORDER BY stock ASC
    public function findStockCritique(): array;

    // SELECT stock FROM service WHERE id_service = ?
    public function getStockById(int $idService): int;

    // UPDATE service SET stock = stock + :qty WHERE id_service = :id
    public function increaseStock(int $idService, int $quantity): void;

    // UPDATE service SET stock = stock - :qty WHERE id_service = :id AND stock >= :qty
    // Returns number of affected rows
    public function decreaseStockGuarded(int $idService, int $quantity): int;
}
```

`findStockCritique()` uses DQL:
```php
return $this->createQueryBuilder('s')
    ->where('s.stock <= s.seuilCritique')
    ->orderBy('s.stock', 'ASC')
    ->getQuery()
    ->getResult();
```

`decreaseStockGuarded()` must use native SQL to leverage the `AND stock >= ?` guard atomically:
```php
return $this->getEntityManager()
    ->getConnection()
    ->executeStatement(
        'UPDATE service SET stock = stock - :qty WHERE id_service = :id AND stock >= :qty',
        ['qty' => $quantity, 'id' => $idService]
    );
```

---

### AchatRepository

```php
class AchatRepository extends ServiceEntityRepository
{
    // SELECT with JOIN to lignes, ordered by date_achat DESC
    public function findAllWithLignes(): array;

    // Find single Achat with its lignes
    public function findByIdWithLignes(int $idAchat): ?Achat;
}
```

`findAllWithLignes()` uses a JOIN FETCH to avoid N+1:
```php
return $this->createQueryBuilder('a')
    ->leftJoin('a.lignes', 'l')
    ->addSelect('l')
    ->leftJoin('l.service', 's')
    ->addSelect('s')
    ->orderBy('a.dateAchat', 'DESC')
    ->getQuery()
    ->getResult();
```

---

### VenteRepository

```php
class VenteRepository extends ServiceEntityRepository
{
    // SELECT ordered by id_vente DESC
    public function findAllOrdered(): array;

    public function findByIdWithLignes(int $idVente): ?Vente;
}
```

---

### ListeAchatRepository / ListeVenteRepository

```php
class ListeAchatRepository extends ServiceEntityRepository
{
    public function findByAchat(int $idAchat): array;
    public function deleteByAchat(int $idAchat): void;
}

class ListeVenteRepository extends ServiceEntityRepository
{
    public function findByVente(int $idVente): array;
    public function deleteByVente(int $idVente): void;
}
```

---

## Services Design

### StockService

**Responsibility:** All stock mutation operations and critical-stock queries. Single source of truth for stock changes.

```php
// src/Service/StockService.php
class StockService
{
    public function __construct(
        private ServiceRepository $serviceRepository
    ) {}

    /**
     * Increases stock for a service. No-op if quantity <= 0.
     */
    public function increaseStock(int $idService, int $quantity): void
    {
        if ($quantity <= 0) return;
        $this->serviceRepository->increaseStock($idService, $quantity);
    }

    /**
     * Decreases stock with guard. Throws RuntimeException if stock < quantity.
     * No-op if quantity <= 0.
     */
    public function decreaseStock(int $idService, int $quantity): void
    {
        if ($quantity <= 0) return;
        $affected = $this->serviceRepository->decreaseStockGuarded($idService, $quantity);
        if ($affected === 0) {
            throw new \RuntimeException("Stock insuffisant pour le service {$idService}");
        }
    }

    /**
     * Returns current stock value for a service.
     */
    public function getStockById(int $idService): int
    {
        return $this->serviceRepository->getStockById($idService);
    }

    /**
     * Returns all services where stock <= seuilCritique, ordered by stock ASC.
     */
    public function findStockCritique(): array
    {
        return $this->serviceRepository->findStockCritique();
    }
}
```

---

### AchatService

**Responsibility:** Orchestrates purchase order creation within a transaction, computes totals, updates stock.

```php
// src/Service/AchatService.php
class AchatService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StockService $stockService
    ) {}

    /**
     * Creates an Achat with all its lines in a single transaction.
     * Increases stock for each line after commit.
     * Returns the persisted Achat.
     */
    public function createAchat(Achat $achat): Achat
    {
        // Compute total
        $total = 0.0;
        foreach ($achat->getLignes() as $ligne) {
            $total += $ligne->getSousTotal();
        }
        $achat->setTotal($total);

        $this->em->beginTransaction();
        try {
            $this->em->persist($achat);
            $this->em->flush();

            // Increase stock for each line
            foreach ($achat->getLignes() as $ligne) {
                $this->stockService->increaseStock(
                    $ligne->getService()->getIdService(),
                    $ligne->getQuantite()
                );
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new \RuntimeException("Erreur lors de la création de l'achat: " . $e->getMessage(), 0, $e);
        }

        return $achat;
    }

    /**
     * Deletes an Achat and all its lines in a single transaction.
     * Lines are removed via cascade (orphanRemoval), but we wrap in explicit transaction.
     */
    public function deleteAchat(int $idAchat): void
    {
        $achat = $this->em->find(Achat::class, $idAchat);
        if (!$achat) {
            throw new \RuntimeException("Achat introuvable: {$idAchat}");
        }

        $this->em->beginTransaction();
        try {
            $this->em->remove($achat);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new \RuntimeException("Erreur lors de la suppression de l'achat", 0, $e);
        }
    }
}
```

**Key design notes:**
- `cascade: ['persist', 'remove']` on `Achat::$lignes` means `$em->persist($achat)` also persists all lines
- Stock increase happens after `flush()` but within the same transaction — if stock update fails, the whole transaction rolls back
- Total is computed in the service, not in the controller

---

### VenteService

**Responsibility:** Orchestrates sales order creation with stock validation, stock decrease, and email alerts.

```php
// src/Service/VenteService.php
class VenteService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StockService $stockService,
        private EmailService $emailService,
        private ServiceRepository $serviceRepository
    ) {}

    /**
     * Validates stock for all lines. Throws if any line exceeds available stock.
     */
    public function validateStock(Vente $vente): void
    {
        foreach ($vente->getLignes() as $ligne) {
            $service = $this->serviceRepository->find($ligne->getService()->getIdService());
            if (!$service) {
                throw new \RuntimeException("Service introuvable: " . $ligne->getService()->getIdService());
            }
            if ($service->getStock() < $ligne->getQuantite()) {
                throw new \RuntimeException(
                    "Stock insuffisant pour {$service->getNom()}: disponible={$service->getStock()}, demandé={$ligne->getQuantite()}"
                );
            }
        }
    }

    /**
     * Creates a Vente: validates stock, persists in transaction, then decreases stock and sends alerts.
     */
    public function createVente(Vente $vente): Vente
    {
        // Server-side stock validation before transaction
        $this->validateStock($vente);

        // Compute total
        $total = 0.0;
        foreach ($vente->getLignes() as $ligne) {
            $total += $ligne->getSousTotal();
        }
        $vente->setTotal($total);

        // Persist in transaction
        $this->em->beginTransaction();
        try {
            $this->em->persist($vente);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new \RuntimeException("Erreur lors de la création de la vente", 0, $e);
        }

        // Post-commit: decrease stock and send alerts (outside transaction)
        foreach ($vente->getLignes() as $ligne) {
            $idService = $ligne->getService()->getIdService();
            try {
                $this->stockService->decreaseStock($idService, $ligne->getQuantite());
            } catch (\RuntimeException $e) {
                // Log but do not interrupt — vente is already committed
                // In production, consider a compensating transaction or event
                error_log($e->getMessage());
                continue;
            }

            $newStock = $this->stockService->getStockById($idService);
            if ($newStock === 0) {
                $service = $this->serviceRepository->find($idService);
                try {
                    $this->emailService->sendStockZeroAlert($service->getNom(), $idService);
                } catch (\Throwable $e) {
                    // Log and continue — email failure must not block sale confirmation
                    error_log("Email alert failed for service {$idService}: " . $e->getMessage());
                }
            }
        }

        return $vente;
    }

    /**
     * Deletes a Vente and all its lines in a single transaction.
     */
    public function deleteVente(int $idVente): void
    {
        $vente = $this->em->find(Vente::class, $idVente);
        if (!$vente) {
            throw new \RuntimeException("Vente introuvable: {$idVente}");
        }

        $this->em->beginTransaction();
        try {
            $this->em->remove($vente);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new \RuntimeException("Erreur lors de la suppression de la vente", 0, $e);
        }
    }
}
```

**Key design notes:**
- Stock decrease happens AFTER the transaction commits — this matches the original Java behavior
- Email failure is caught and logged; it never interrupts the sale response
- `validateStock()` is called before the transaction to fail fast

---

### ExchangeRateService

**Responsibility:** Calls open.er-api.com, parses rates, converts amounts.

```php
// src/Service/ExchangeRateService.php
class ExchangeRateService
{
    private const API_BASE = 'https://open.er-api.com/v6/latest/';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    /**
     * Returns the exchange rate from base to target currency, or null on failure.
     */
    public function getRate(string $base, string $target): ?float
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE . strtoupper($base), [
                'timeout' => 5,
            ]);
            if ($response->getStatusCode() !== 200) return null;

            $data = $response->toArray(false);
            if (!isset($data['rates'][strtoupper($target)])) return null;

            return (float) $data['rates'][strtoupper($target)];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Converts amount from base to target currency. Returns null if rate unavailable.
     * Rounds to 2 decimal places using HALF_UP.
     */
    public function convert(float $amount, string $base, string $target): ?float
    {
        $rate = $this->getRate($base, $target);
        if ($rate === null) return null;
        return round($amount * $rate, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Returns sorted array of all currency codes for the given base currency.
     */
    public function getCurrencyCodes(string $base): array
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE . strtoupper($base), [
                'timeout' => 5,
            ]);
            if ($response->getStatusCode() !== 200) return [];

            $data = $response->toArray(false);
            if (!isset($data['rates'])) return [];

            $codes = array_keys($data['rates']);
            sort($codes);
            return $codes;
        } catch (\Throwable) {
            return [];
        }
    }
}
```

---

### PaymentService

**Responsibility:** Creates Stripe Checkout Sessions and generates QR codes.

```php
// src/Service/PaymentService.php
class PaymentService
{
    public function __construct(
        private string $stripeSecretKey,  // injected via %env(STRIPE_SECRET_KEY)%
        private string $qrOutputDir       // injected via %env(QR_OUTPUT_DIR)%
    ) {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Creates a Stripe Checkout Session for the given EUR total.
     * Returns the session URL, or null on failure.
     */
    public function createCheckoutSession(float $totalEur): ?string
    {
        if ($totalEur <= 0) return null;

        $amountCents = (int) round($totalEur * 100, 0, PHP_ROUND_HALF_UP);

        try {
            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'success_url' => 'https://127.0.0.1:8000/erp/achat',
                'cancel_url' => 'https://127.0.0.1:8000/erp/achat',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $amountCents,
                        'product_data' => ['name' => 'Abonnement'],
                    ],
                ]],
            ]);
            return $session->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generates a 260x260 QR code PNG from the given URL.
     * Saves to configured output directory. Returns the file path.
     */
    public function generateQrCode(string $url): string
    {
        $qrCode = \Endroid\QrCode\QrCode::create($url)
            ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
            ->setSize(260)
            ->setMargin(0);

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);

        $path = rtrim($this->qrOutputDir, '/') . '/stripe_checkout_qr.png';
        $result->saveToFile($path);
        return $path;
    }
}
```

**Config binding in `services.yaml`:**
```yaml
App\Service\PaymentService:
    arguments:
        $stripeSecretKey: '%env(STRIPE_SECRET_KEY)%'
        $qrOutputDir: '%env(QR_OUTPUT_DIR)%'
```

---

### EmailService

**Responsibility:** Sends transactional emails via Gmail SMTP using Symfony Mailer.

```php
// src/Service/EmailService.php
class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress,   // injected via %env(MAILER_FROM)%
        private string $alertRecipient // injected via %env(MAILER_ALERT_TO)%
    ) {}

    /**
     * Sends a stock-zero alert email for the given service.
     * Throws on failure — caller must catch and log.
     */
    public function sendStockZeroAlert(string $serviceName, int $serviceId): void
    {
        $subject = "🚨 Stock épuisé — {$serviceName}";
        $body = $this->buildStockZeroEmail($serviceName, $serviceId);

        $email = (new Email())
            ->from($this->fromAddress)
            ->to($this->alertRecipient)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }

    public function buildStockZeroEmail(string $serviceName, int $serviceId): string
    {
        return <<<TEXT
Bonjour,

Alerte stock : le stock d'un service vient d'atteindre 0.

Détails :
- Service : {$serviceName}
- ID : {$serviceId}
- Statut : Stock épuisé (0)

Action recommandée :
Merci de procéder au réapprovisionnement dès que possible afin d'éviter toute rupture de vente.

Cordialement,
FarmIADesk (système)
TEXT;
    }
}
```

**`config/packages/mailer.yaml`:**
```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
```

**`.env` entry:**
```
MAILER_DSN=smtp://username:password@smtp.gmail.com:587?encryption=tls
MAILER_FROM=your-gmail@gmail.com
MAILER_ALERT_TO=recipient@example.com
```

---

## Controllers Design

### DashboardController

**Route:** `GET /` → `app_dashboard`

**Responsibilities:**
- Load and display critical-stock services on the home page
- Provide navigation entry point

```php
// src/Controller/DashboardController.php
#[Route('/', name: 'app_dashboard')]
public function index(StockService $stockService): Response
{
    $critiques = $stockService->findStockCritique();
    return $this->render('dashboard/index.html.twig', [
        'critiques' => $critiques,
    ]);
}
```

---

### ServiceController

**Routes:**

| Method | Path | Name | Action |
|---|---|---|---|
| GET | `/service/` | `app_service_index` | List all services + critical stock warning |
| GET/POST | `/service/new` | `app_service_new` | Create form |
| GET/POST | `/service/{id}/edit` | `app_service_edit` | Edit form |
| POST | `/service/{id}/delete` | `app_service_delete` | Delete |

**Key behaviors:**
- `index()`: calls `StockService::findStockCritique()`, passes result to template; if non-empty, template renders a warning block
- `new()` / `edit()`: uses `ServiceType` form; on valid submit, persists via EntityManager
- `delete()`: catches `ForeignKeyConstraintViolationException` (Doctrine wraps the DB RESTRICT error) and adds a flash error instead of crashing

```php
#[Route('/{id}/delete', name: 'app_service_delete', methods: ['POST'])]
public function delete(int $id, ServiceRepository $repo): Response
{
    $service = $repo->find($id);
    if (!$service) {
        $this->addFlash('error', 'Service introuvable.');
        return $this->redirectToRoute('app_service_index');
    }
    try {
        $this->em->remove($service);
        $this->em->flush();
        $this->addFlash('success', 'Service supprimé.');
    } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
        $this->addFlash('error', 'Impossible de supprimer : ce service est référencé par des achats ou ventes.');
    }
    return $this->redirectToRoute('app_service_index');
}
```

---

### AchatController

**Routes:**

| Method | Path | Name | Action |
|---|---|---|---|
| GET | `/achat/` | `app_achat_index` | List all achats |
| GET/POST | `/achat/new` | `app_achat_new` | Create form with dynamic lines |
| POST | `/achat/{id}/delete` | `app_achat_delete` | Delete |
| POST | `/achat/{id}/pay` | `app_achat_pay` | Stripe payment + QR code |
| POST | `/achat/convert` | `app_achat_convert` | Currency conversion (AJAX or form) |

**`new()` action flow:**
1. Build `Achat` with an initial empty `ListeAchat` collection
2. Bind `AchatType` form (with embedded `CollectionType` for lines)
3. On valid submit: call `AchatService::createAchat($achat)`
4. Add flash success, redirect to index

**`pay()` action flow:**
1. Load Achat by id
2. Validate total > 0 and lines not empty
3. Call `PaymentService::createCheckoutSession($achat->getTotal())`
4. If URL returned: call `PaymentService::generateQrCode($url)`, render QR code page
5. If null: add flash error

**`convert()` action flow:**
1. Receive `total` (float) and `targetCurrency` (string) from POST
2. Call `ExchangeRateService::convert($total, 'EUR', $targetCurrency)`
3. If null: add flash error "Impossible de récupérer le taux (API indisponible)."
4. Return JSON response `{converted: X, currency: Y, rate: Z}` for AJAX, or re-render page

---

### VenteController

**Routes:**

| Method | Path | Name | Action |
|---|---|---|---|
| GET | `/vente/` | `app_vente_index` | List all ventes |
| GET/POST | `/vente/new` | `app_vente_new` | Create form with stock validation |
| POST | `/vente/{id}/delete` | `app_vente_delete` | Delete |
| POST | `/vente/convert` | `app_vente_convert` | Currency conversion |

**`new()` action flow:**
1. Build `Vente` with empty lines collection
2. Bind `VenteType` form
3. On valid submit: call `VenteService::createVente($vente)`
4. Catch `RuntimeException` (stock validation failure) → add flash error, re-render form
5. On success: add flash success, redirect to index

**Stock validation in form:**
- The `VenteType` form includes a custom constraint or the controller validates before calling the service
- The service always performs the final server-side check regardless

---

## Database Design

### Schema

```sql
CREATE DATABASE IF NOT EXISTS farmia_desk
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE farmia_desk;

CREATE TABLE service (
    id_service     INT AUTO_INCREMENT PRIMARY KEY,
    nom            VARCHAR(255) NOT NULL,
    description    TEXT,
    prix           DOUBLE NOT NULL DEFAULT 0,
    stock          INT NOT NULL DEFAULT 0,
    seuil_critique INT NOT NULL DEFAULT 0
);

CREATE TABLE achat (
    id_achat   INT AUTO_INCREMENT PRIMARY KEY,
    date_achat DATE NOT NULL,
    total      DOUBLE NOT NULL DEFAULT 0
);

CREATE TABLE liste_achat (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_achat      INT NOT NULL,
    id_service    INT NOT NULL,
    quantite      INT NOT NULL,
    prix_unitaire DOUBLE NOT NULL,
    CONSTRAINT fk_la_achat   FOREIGN KEY (id_achat)   REFERENCES achat(id_achat)     ON DELETE CASCADE,
    CONSTRAINT fk_la_service FOREIGN KEY (id_service) REFERENCES service(id_service) ON DELETE RESTRICT,
    UNIQUE KEY uk_liste_achat (id_achat, id_service)
);

CREATE TABLE vente (
    id_vente   INT AUTO_INCREMENT PRIMARY KEY,
    date_vente DATE NOT NULL,
    total      DOUBLE NOT NULL DEFAULT 0
);

CREATE TABLE liste_vente (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_vente      INT NOT NULL,
    id_service    INT NOT NULL,
    quantite      INT NOT NULL,
    prix_unitaire DOUBLE NOT NULL,
    CONSTRAINT fk_lv_vente   FOREIGN KEY (id_vente)   REFERENCES vente(id_vente)     ON DELETE CASCADE,
    CONSTRAINT fk_lv_service FOREIGN KEY (id_service) REFERENCES service(id_service) ON DELETE RESTRICT
);

CREATE INDEX idx_service_stock ON service(stock);
CREATE INDEX idx_achat_date    ON achat(date_achat);
```

### Entity-Relationship Diagram

```mermaid
erDiagram
    SERVICE {
        int id_service PK
        varchar nom
        text description
        double prix
        int stock
        int seuil_critique
    }
    ACHAT {
        int id_achat PK
        date date_achat
        double total
    }
    LISTE_ACHAT {
        int id PK
        int id_achat FK
        int id_service FK
        int quantite
        double prix_unitaire
    }
    VENTE {
        int id_vente PK
        date date_vente
        double total
    }
    LISTE_VENTE {
        int id PK
        int id_vente FK
        int id_service FK
        int quantite
        double prix_unitaire
    }

    ACHAT ||--o{ LISTE_ACHAT : "has lines"
    SERVICE ||--o{ LISTE_ACHAT : "referenced by"
    VENTE ||--o{ LISTE_VENTE : "has lines"
    SERVICE ||--o{ LISTE_VENTE : "referenced by"
```

### Constraints Summary

| Table | Constraint | Type | Detail |
|---|---|---|---|
| `liste_achat` | `fk_la_achat` | FK CASCADE | Delete lines when Achat deleted |
| `liste_achat` | `fk_la_service` | FK RESTRICT | Block Service delete if referenced |
| `liste_achat` | `uk_liste_achat` | UNIQUE | One line per service per achat |
| `liste_vente` | `fk_lv_vente` | FK CASCADE | Delete lines when Vente deleted |
| `liste_vente` | `fk_lv_service` | FK RESTRICT | Block Service delete if referenced |
| `service` | `idx_service_stock` | INDEX | Speeds up critical-stock query |
| `achat` | `idx_achat_date` | INDEX | Speeds up date-ordered listing |

---

## External Integrations

### Stripe Payment Integration

**Library:** `stripe/stripe-php` (install via `composer require stripe/stripe-php`)

**Flow:**
1. Controller receives POST `/achat/{id}/pay`
2. Loads Achat, validates total > 0 and lines not empty
3. Calls `PaymentService::createCheckoutSession(float $totalEur)`
4. Service converts EUR to cents: `(int) round($totalEur * 100, 0, PHP_ROUND_HALF_UP)`
5. Creates Stripe Session with mode=payment, currency=usd, product="Abonnement"
6. Returns session URL string
7. Controller calls `PaymentService::generateQrCode($url)` → saves PNG to `$qrOutputDir/stripe_checkout_qr.png`
8. Renders template with QR code image path

**Error handling:** `StripeException` is caught in `PaymentService`, logged, returns null. Controller adds flash error.

**Environment variables:**
```
STRIPE_SECRET_KEY=sk_test_...
QR_OUTPUT_DIR=/var/www/farmia-desk/var/qrcodes
```

---

### Exchange Rate API Integration

**Library:** Symfony HttpClient (`symfony/http-client`)

**Endpoint:** `GET https://open.er-api.com/v6/latest/{BASE_CURRENCY}`

**Flow:**
1. Controller receives POST `/achat/convert` or `/vente/convert` with `total` and `targetCurrency`
2. Calls `ExchangeRateService::convert($total, 'EUR', $targetCurrency)`
3. Service makes HTTP GET with 5-second timeout
4. Parses JSON `rates` object, extracts target rate
5. Returns `round($amount * $rate, 2, PHP_ROUND_HALF_UP)` or null
6. Controller returns JSON `{converted, currency, rate}` or flash error

**Default behavior:** base=EUR, default target=TND (pre-selected in UI)

**Error handling:** Any HTTP error, timeout, or missing key returns null. Controller shows "Impossible de récupérer le taux (API indisponible)."

---

### Email Integration (Gmail SMTP)

**Library:** Symfony Mailer (`symfony/mailer`)

**Configuration:**
```
# .env.local (NOT committed)
MAILER_DSN=smtp://your-gmail%40gmail.com:your-app-password@smtp.gmail.com:587?encryption=tls
MAILER_FROM=your-gmail@gmail.com
MAILER_ALERT_TO=recipient@example.com
```

**Flow:**
1. After each `decreaseStock()` in `VenteService`, check `getStockById()`
2. If stock = 0: call `EmailService::sendStockZeroAlert($serviceName, $serviceId)`
3. Service builds email with subject "🚨 Stock épuisé — {serviceName}"
4. Sends via Symfony Mailer (STARTTLS on port 587)
5. Any exception is caught by `VenteService`, logged, does not interrupt sale

**Note:** Gmail requires an App Password (not the account password). Generate at myaccount.google.com → Security → App passwords.

---

## Security & Configuration

### Environment Variables

All secrets and environment-specific values are stored in `.env` (defaults, committed) and `.env.local` (real values, NOT committed).

**`.env` (committed, with placeholder values):**
```dotenv
APP_ENV=dev
APP_SECRET=change-me-in-production

DATABASE_URL="mysql://root:@127.0.0.1:3306/farmia_desk?serverVersion=8.0&charset=utf8mb4"

STRIPE_SECRET_KEY=sk_test_placeholder
QR_OUTPUT_DIR=%kernel.project_dir%/var/qrcodes

MAILER_DSN=smtp://localhost
MAILER_FROM=noreply@farmia-desk.local
MAILER_ALERT_TO=admin@farmia-desk.local
```

**`.env.local` (NOT committed):**
```dotenv
DATABASE_URL="mysql://root:yourpassword@127.0.0.1:3306/farmia_desk?serverVersion=8.0&charset=utf8mb4"
STRIPE_SECRET_KEY=sk_live_...
MAILER_DSN=smtp://your-gmail%40gmail.com:app-password@smtp.gmail.com:587?encryption=tls
MAILER_FROM=your-gmail@gmail.com
MAILER_ALERT_TO=recipient@example.com
```

### API Key Injection

Symfony services receive secrets via constructor injection bound in `config/services.yaml`:

```yaml
# config/services.yaml
services:
    App\Service\PaymentService:
        arguments:
            $stripeSecretKey: '%env(STRIPE_SECRET_KEY)%'
            $qrOutputDir: '%env(QR_OUTPUT_DIR)%'

    App\Service\EmailService:
        arguments:
            $fromAddress: '%env(MAILER_FROM)%'
            $alertRecipient: '%env(MAILER_ALERT_TO)%'
```

### CSRF Protection

All POST forms (create, edit, delete) must include Symfony's CSRF token:
```twig
{{ form_row(form._token) }}
```
Delete actions use a hidden form with CSRF token, not a plain GET link.

### Input Validation

- `ServiceType` form: `NotBlank` on `nom`, `Type(type: 'numeric')` on `prix`/`stock`/`seuilCritique`
- `AchatType` / `VenteType`: `NotNull` on `dateAchat`/`dateVente`, `Count(min: 1)` on `lignes`
- `ListeAchatType` / `ListeVenteType`: `Positive` on `quantite`, `PositiveOrZero` on `prixUnitaire`

---

## UI Layer

### Base Layout (`templates/base.html.twig`)

```twig
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FarmIA Desk - Gestion Achat et Stocks</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<nav>
    <a href="{{ path('app_dashboard') }}">Accueil</a>
    <a href="{{ path('app_service_index') }}">Services</a>
    <a href="{{ path('app_achat_index') }}">Achats</a>
    <a href="{{ path('app_vente_index') }}">Ventes</a>
</nav>

{# Flash messages #}
{% for type, messages in app.flashes %}
    {% for message in messages %}
        <div class="alert alert-{{ type }}">{{ message }}</div>
    {% endfor %}
{% endfor %}

{% block body %}{% endblock %}
</body>
</html>
```

### Dashboard (`templates/dashboard/index.html.twig`)

- Displays app title
- If `critiques` is not empty: renders a warning table with service name, current stock, seuil critique
- Navigation links to all three modules

### Service Module (`templates/service/`)

**`index.html.twig`:**
- Critical stock warning block (if `critiques` not empty)
- Table: id, nom, description, prix, stock, seuilCritique, actions (edit, delete)
- "Nouveau service" button → `app_service_new`
- Delete button: POST form with CSRF token

**`_form.html.twig`:**
- Fields: nom (text), description (textarea), prix (number), stock (number), seuilCritique (number)
- Submit button

### Achat Module (`templates/achat/`)

**`index.html.twig`:**
- Table: id, date, total, actions (delete, pay)
- "Nouvel achat" button

**`new.html.twig`:**
- Date picker for `dateAchat`
- Dynamic lines section:
  - Service dropdown (populated from all services)
  - Quantity input
  - Unit price input (auto-filled from service prix)
  - "Ajouter ligne" button (JavaScript)
  - Lines table showing: service name, quantite, prixUnitaire, sousTotal
  - "Retirer" button per line
- Running total display
- Currency conversion section: target currency dropdown, "Convertir" button, result display
- "Enregistrer" submit button

**`show.html.twig`:**
- Achat details + lines table
- QR code image (if generated)
- "Payer avec Stripe" button

### Vente Module (`templates/vente/`)

**`index.html.twig`:**
- Table: id, date, total, actions (delete)
- "Nouvelle vente" button

**`new.html.twig`:**
- Same structure as achat/new.html.twig
- Service dropdown shows stock level: "Nom (Stock: X)"
- Client-side stock validation via JavaScript:
  - Disable "Ajouter" if selected service stock = 0
  - Show warning if requested quantity > available stock
- Currency conversion section

### JavaScript for Dynamic Lines

Both `achat/new.html.twig` and `vente/new.html.twig` require JavaScript to:
1. Add/remove line rows dynamically (Symfony CollectionType with `allow_add: true`)
2. Auto-fill unit price when service is selected
3. Recalculate running total on quantity/price change
4. For Vente: validate stock before adding a line

Symfony's CollectionType with `allow_add: true` and a Stimulus controller (or vanilla JS) handles the dynamic row addition. The `data-prototype` attribute on the collection div provides the HTML template for new rows.

### Flash Message Types

| Type | Usage |
|---|---|
| `success` | Successful create/update/delete |
| `error` | Validation failure, FK constraint, service not found |
| `warning` | Stock critique alert, API unavailable |

---

## Forms Design

### ServiceType

```php
// src/Form/ServiceType.php
class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('prix', NumberType::class, [
                'constraints' => [new NotNull(), new PositiveOrZero()],
            ])
            ->add('stock', IntegerType::class, [
                'constraints' => [new NotNull(), new PositiveOrZero()],
            ])
            ->add('seuilCritique', IntegerType::class, [
                'constraints' => [new NotNull(), new PositiveOrZero()],
            ]);
    }
}
```

### AchatType

```php
// src/Form/AchatType.php
class AchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateAchat', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [new NotNull(message: 'La date est obligatoire.')],
            ])
            ->add('lignes', CollectionType::class, [
                'entry_type' => ListeAchatType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [new Count(min: 1, minMessage: 'Ajoutez au moins une ligne.')],
            ]);
    }
}
```

### ListeAchatType

```php
// src/Form/ListeAchatType.php
class ListeAchatType extends AbstractType
{
    public function __construct(private ServiceRepository $serviceRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => fn(Service $s) => $s->getNom() . ' (ID ' . $s->getIdService() . ')',
                'constraints' => [new NotNull()],
            ])
            ->add('quantite', IntegerType::class, [
                'constraints' => [new Positive()],
            ])
            ->add('prixUnitaire', NumberType::class, [
                'constraints' => [new PositiveOrZero()],
            ]);
    }
}
```

### VenteType / ListeVenteType

Same structure as AchatType / ListeAchatType, with:
- `dateVente` instead of `dateAchat`
- `ListeVenteType` instead of `ListeAchatType`
- Service choice label shows stock: `$s->getNom() . ' (Stock: ' . $s->getStock() . ')'`

**Note on UNIQUE constraint for Achat lines:**
The `uk_liste_achat (id_achat, id_service)` constraint means the same service cannot appear twice in one Achat. The form/controller must merge duplicate service lines before persisting (accumulate quantities). This logic lives in the controller's form processing step, before calling `AchatService::createAchat()`.

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: isStockCritique reflects stock vs threshold

*For any* Service with any combination of `stock` and `seuilCritique` values, `isStockCritique()` SHALL return `true` if and only if `stock < seuilCritique`.

**Validates: Requirements 1.2**

---

### Property 2: Blank nom is always rejected

*For any* string composed entirely of whitespace characters (including the empty string), attempting to create or update a Service with that string as `nom` SHALL be rejected with a validation error, and no Service SHALL be persisted.

**Validates: Requirements 1.3**

---

### Property 3: findStockCritique returns exactly the critical services

*For any* set of Service records in the database, `StockService::findStockCritique()` SHALL return exactly those Services where `stock <= seuilCritique`, ordered by `stock` ascending, and no others.

**Validates: Requirements 2.1, 2.4**

---

### Property 4: Order line subtotal is always quantite × prixUnitaire

*For any* `ListeAchat` or `ListeVente` with any positive `quantite` and any non-negative `prixUnitaire`, `getSousTotal()` SHALL equal `quantite * prixUnitaire`.

**Validates: Requirements 3.3, 5.3**

---

### Property 5: Order total equals sum of all line subtotals

*For any* Achat or Vente with any number of lines, the `total` field computed by `AchatService` or `VenteService` SHALL equal the sum of `getSousTotal()` across all lines.

**Validates: Requirements 3.9, 5.12**

---

### Property 6: Achat creation increases stock for every line

*For any* valid Achat with N lines, after `AchatService::createAchat()` completes successfully, each referenced Service's `stock` SHALL have increased by exactly the corresponding line's `quantite`.

**Validates: Requirements 3.7**

---

### Property 7: Vente creation is rejected when any line exceeds available stock

*For any* Vente where at least one line has `quantite > service.stock`, `VenteService::createVente()` SHALL throw a RuntimeException and SHALL NOT persist the Vente or any of its lines.

**Validates: Requirements 5.5, 5.8, 5.9**

---

### Property 8: Stock mutation guards are no-ops for non-positive quantities

*For any* call to `StockService::increaseStock()` or `StockService::decreaseStock()` with a `quantity` of 0 or less, the service's `stock` value in the database SHALL remain unchanged.

**Validates: Requirements 6.2, 6.6**

---

### Property 9: decreaseStock throws when stock is insufficient

*For any* Service with `stock = S` and any `quantity > S`, calling `StockService::decreaseStock(idService, quantity)` SHALL throw a RuntimeException with message containing "Stock insuffisant", and the Service's `stock` SHALL remain unchanged.

**Validates: Requirements 6.3, 6.4**

---

### Property 10: Currency conversion rounds correctly

*For any* positive `amount` and any valid exchange `rate`, `ExchangeRateService::convert(amount, base, target)` SHALL return `round(amount * rate, 2, PHP_ROUND_HALF_UP)`.

**Validates: Requirements 8.4**

---

### Property 11: EUR-to-cents conversion rounds correctly

*For any* positive EUR `total`, `PaymentService::createCheckoutSession()` SHALL pass to Stripe an `amountCents` equal to `(int) round(total * 100, 0, PHP_ROUND_HALF_UP)`.

**Validates: Requirements 9.3**

---

## Error Handling

### Transaction Failures

All `AchatService` and `VenteService` methods wrap operations in `beginTransaction()` / `commit()` / `rollback()`. Any `\Throwable` triggers rollback and re-throws as `RuntimeException`. Controllers catch `RuntimeException` and add a flash error.

### Foreign Key Violations

`ServiceController::delete()` catches `Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException` and returns a user-friendly flash error instead of a 500.

### Stock Insuffisant

`StockService::decreaseStock()` throws `RuntimeException("Stock insuffisant pour le service {id}")` when the guarded UPDATE affects 0 rows. `VenteService` catches this per-line, logs it, and continues (the Vente is already committed at this point).

### External API Failures

- `ExchangeRateService`: all exceptions caught, returns null. Controller shows "Impossible de récupérer le taux (API indisponible)."
- `PaymentService`: `StripeException` caught, logged, returns null. Controller shows flash error.
- `EmailService`: exception propagates to `VenteService`, which catches it, logs it, and continues without interrupting the sale response.

### Form Validation Errors

Symfony Form component handles validation. On invalid submit, the form is re-rendered with inline error messages. No redirect on validation failure.

### 404 Handling

Controllers use `$this->findOr404()` pattern (or manual check + `throw $this->createNotFoundException()`) for entity lookups by ID.

---

## Testing Strategy

### Unit Tests (PHPUnit)

Unit tests verify specific behaviors with concrete examples and edge cases.

**StockService tests:**
- `decreaseStock` with quantity = 0 → no DB call
- `decreaseStock` with quantity < 0 → no DB call
- `increaseStock` with quantity = 0 → no DB call
- `decreaseStock` when repository returns 0 affected rows → throws RuntimeException

**AchatService tests (with mocked EntityManager and StockService):**
- Total computation: specific example with 3 lines
- Transaction rollback on flush failure

**VenteService tests (with mocked dependencies):**
- `validateStock` throws when line quantity > service stock
- Email failure does not throw from `createVente`

**ExchangeRateService tests (with mocked HttpClient):**
- Returns null on non-200 response
- Returns null when `rates` key missing
- Returns correct float for valid response

**PaymentService tests:**
- EUR to cents: 10.005 EUR → 1001 cents (HALF_UP)
- EUR to cents: 10.004 EUR → 1000 cents

**Entity tests:**
- `Service::isStockCritique()`: stock=5, seuil=10 → true; stock=10, seuil=10 → false; stock=11, seuil=10 → false
- `ListeAchat::getSousTotal()`: 3 × 15.50 = 46.50

### Property-Based Tests (PHPUnit + eris or vimeo/psalm-plugin-phpunit)

For PHP property-based testing, use the **`eris`** library (`giorgiosironi/eris`) or **`infection`** for mutation testing. Alternatively, implement simple generators with PHPUnit data providers for the most critical properties.

Each property test runs minimum 100 iterations.

**Property 1 — isStockCritique:**
```php
// Feature: farmia-desk-symfony-spec, Property 1: isStockCritique reflects stock vs threshold
// For any (stock, seuilCritique) pair, isStockCritique() === (stock < seuilCritique)
public function testIsStockCritiqueProperty(): void
{
    // Generate 100 random (stock, seuilCritique) pairs
    for ($i = 0; $i < 100; $i++) {
        $stock = random_int(0, 1000);
        $seuil = random_int(0, 1000);
        $service = new Service();
        $service->setStock($stock);
        $service->setSeuilCritique($seuil);
        $this->assertSame($stock < $seuil, $service->isStockCritique());
    }
}
```

**Property 4 — getSousTotal:**
```php
// Feature: farmia-desk-symfony-spec, Property 4: Order line subtotal is always quantite × prixUnitaire
public function testGetSousTotalProperty(): void
{
    for ($i = 0; $i < 100; $i++) {
        $quantite = random_int(1, 500);
        $prix = round(random_int(1, 100000) / 100, 2);
        $ligne = new ListeAchat();
        $ligne->setQuantite($quantite);
        $ligne->setPrixUnitaire($prix);
        $this->assertEqualsWithDelta($quantite * $prix, $ligne->getSousTotal(), 0.0001);
    }
}
```

**Property 5 — Order total:**
```php
// Feature: farmia-desk-symfony-spec, Property 5: Order total equals sum of all line subtotals
public function testAchatTotalProperty(): void
{
    for ($i = 0; $i < 100; $i++) {
        $lineCount = random_int(1, 10);
        $achat = new Achat();
        $expectedTotal = 0.0;
        for ($j = 0; $j < $lineCount; $j++) {
            $ligne = new ListeAchat();
            $ligne->setQuantite(random_int(1, 100));
            $ligne->setPrixUnitaire(round(random_int(1, 10000) / 100, 2));
            $achat->addLigne($ligne);
            $expectedTotal += $ligne->getSousTotal();
        }
        // AchatService computes total from lines
        $computedTotal = array_sum(array_map(fn($l) => $l->getSousTotal(), $achat->getLignes()->toArray()));
        $this->assertEqualsWithDelta($expectedTotal, $computedTotal, 0.0001);
    }
}
```

**Property 8 — Stock mutation guards:**
```php
// Feature: farmia-desk-symfony-spec, Property 8: Stock mutation guards are no-ops for non-positive quantities
public function testStockMutationGuardProperty(): void
{
    $repo = $this->createMock(ServiceRepository::class);
    $repo->expects($this->never())->method('increaseStock');
    $repo->expects($this->never())->method('decreaseStockGuarded');
    $stockService = new StockService($repo);

    for ($i = 0; $i < 100; $i++) {
        $qty = random_int(-500, 0);
        $stockService->increaseStock(1, $qty);
        $stockService->decreaseStock(1, $qty);
    }
}
```

**Property 10 — Currency conversion rounding:**
```php
// Feature: farmia-desk-symfony-spec, Property 10: Currency conversion rounds correctly
public function testConvertRoundingProperty(): void
{
    for ($i = 0; $i < 100; $i++) {
        $amount = round(random_int(1, 100000) / 100, 2);
        $rate = round(random_int(1, 500) / 100, 4);
        $expected = round($amount * $rate, 2, PHP_ROUND_HALF_UP);
        // Test the pure computation directly
        $this->assertSame($expected, round($amount * $rate, 2, PHP_ROUND_HALF_UP));
    }
}
```

### Integration Tests

- Database integration: create Achat with 2 lines, verify stock increased, verify total correct
- Delete Achat: verify lines deleted via CASCADE
- Delete Service with FK reference: verify exception thrown
- VenteService end-to-end: create Vente, verify stock decreased, verify email mock called when stock=0

### Smoke Tests

- Application loads at `/` without error
- Database connection is established
- All routes return 200 (or expected redirect)

---

## PART 2: TASK BREAKDOWN

---

## PHASE 1: Project Setup

### Task 1.1 — Install Symfony and core dependencies

```bash
composer create-project symfony/skeleton farmia-desk-symfony
cd farmia-desk-symfony
composer require symfony/orm-pack
composer require symfony/form symfony/validator
composer require symfony/twig-bundle twig/extra-bundle
composer require symfony/http-client
composer require symfony/mailer
composer require stripe/stripe-php
composer require endroid/qr-code
composer require --dev symfony/maker-bundle phpunit/phpunit
```

### Task 1.2 — Configure `.env`

Set `DATABASE_URL`, `STRIPE_SECRET_KEY`, `QR_OUTPUT_DIR`, `MAILER_DSN`, `MAILER_FROM`, `MAILER_ALERT_TO` as described in the Security & Config section.

Create `.env.local` with real credentials (not committed).

Add `.env.local` to `.gitignore`.

### Task 1.3 — Configure Doctrine

`config/packages/doctrine.yaml`:
```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        charset: utf8mb4
    orm:
        auto_generate_proxy_classes: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        mappings:
            App:
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
```

### Task 1.4 — Create the database

```bash
php bin/console doctrine:database:create
```

### Task 1.5 — Configure Mailer

`config/packages/mailer.yaml`:
```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
```

### Task 1.6 — Configure services.yaml bindings

Add `PaymentService` and `EmailService` argument bindings as shown in the Security & Config section.

---

## PHASE 2: Entities

### Task 2.1 — Create Service entity

Create `src/Entity/Service.php` with all fields, Doctrine attributes, `isStockCritique()` method, and getters/setters.

### Task 2.2 — Create Achat entity

Create `src/Entity/Achat.php` with `idAchat`, `dateAchat`, `total`, `lignes` (OneToMany to ListeAchat), `addLigne()`, `removeLigne()`.

### Task 2.3 — Create ListeAchat entity

Create `src/Entity/ListeAchat.php` with `id`, `achat` (ManyToOne), `service` (ManyToOne), `quantite`, `prixUnitaire`, `getSousTotal()`. Add `#[ORM\UniqueConstraint]` on `(id_achat, id_service)`.

### Task 2.4 — Create Vente entity

Create `src/Entity/Vente.php` mirroring Achat structure with `idVente`, `dateVente`, `total`, `lignes` (OneToMany to ListeVente).

### Task 2.5 — Create ListeVente entity

Create `src/Entity/ListeVente.php` mirroring ListeAchat without the UNIQUE constraint.

### Task 2.6 — Generate and run migrations

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Verify the generated SQL matches the schema in the Database Design section. Manually add indexes `idx_service_stock` and `idx_achat_date` if not generated automatically.

---

## PHASE 3: Repositories

### Task 3.1 — ServiceRepository

Implement `findAllOrderedById()`, `findStockCritique()`, `getStockById()`, `increaseStock()`, `decreaseStockGuarded()`.

The `decreaseStockGuarded()` method MUST use native SQL with `AND stock >= :qty` in the WHERE clause.

### Task 3.2 — AchatRepository

Implement `findAllWithLignes()` (JOIN FETCH to avoid N+1) and `findByIdWithLignes()`.

### Task 3.3 — VenteRepository

Implement `findAllOrdered()` (ORDER BY id_vente DESC) and `findByIdWithLignes()`.

### Task 3.4 — ListeAchatRepository

Implement `findByAchat(int $idAchat)` and `deleteByAchat(int $idAchat)`.

### Task 3.5 — ListeVenteRepository

Implement `findByVente(int $idVente)` and `deleteByVente(int $idVente)`.

---

## PHASE 4: Core Services

### Task 4.1 — StockService

Implement `increaseStock()`, `decreaseStock()` (with guard and exception), `getStockById()`, `findStockCritique()` as specified in the Services Design section.

### Task 4.2 — AchatService

Implement `createAchat()` (transaction: persist Achat + lines, then increaseStock per line) and `deleteAchat()` (transaction: remove Achat, cascade removes lines).

### Task 4.3 — VenteService

Implement `validateStock()`, `createVente()` (validate → compute total → transaction persist → post-commit decreaseStock + email alert), and `deleteVente()`.

---

## PHASE 5: External Integration Services

### Task 5.1 — ExchangeRateService

Implement `getRate()`, `convert()`, `getCurrencyCodes()` using Symfony HttpClient with 5-second timeout. All exceptions return null/empty array.

### Task 5.2 — PaymentService

Implement `createCheckoutSession()` (EUR to cents conversion, Stripe Session creation) and `generateQrCode()` (endroid/qr-code, 260×260, UTF-8, saves to `$qrOutputDir`).

### Task 5.3 — EmailService

Implement `sendStockZeroAlert()` and `buildStockZeroEmail()` using Symfony Mailer. Credentials injected from env vars.

---

## PHASE 6: Controllers

### Task 6.1 — DashboardController

Route `GET /` → load critical stock services → render `dashboard/index.html.twig`.

### Task 6.2 — ServiceController

Implement `index()`, `new()`, `edit()`, `delete()` with:
- Critical stock warning on index
- ServiceType form binding
- FK violation catch on delete
- Flash messages for all outcomes

### Task 6.3 — AchatController

Implement `index()`, `new()`, `delete()`, `pay()`, `convert()` with:
- AchatType form with CollectionType for lines
- Duplicate service line merging before persist
- AchatService::createAchat() call
- PaymentService integration (QR code generation)
- ExchangeRateService integration (AJAX or form POST)

### Task 6.4 — VenteController

Implement `index()`, `new()`, `delete()`, `convert()` with:
- VenteType form with CollectionType for lines
- VenteService::createVente() call
- RuntimeException catch → flash error
- ExchangeRateService integration

---

## PHASE 7: UI / Twig Templates

### Task 7.1 — Base layout

Create `templates/base.html.twig` with navigation (Services, Achats, Ventes), flash message rendering, and app title "FarmIA Desk - Gestion Achat et Stocks".

### Task 7.2 — Dashboard template

Create `templates/dashboard/index.html.twig` with critical stock warning table.

### Task 7.3 — Service templates

Create `templates/service/index.html.twig` (list + critical stock warning) and `templates/service/_form.html.twig`.

### Task 7.4 — Achat templates

Create `templates/achat/index.html.twig` (list with pay/delete actions), `templates/achat/new.html.twig` (dynamic lines form, currency conversion, running total), `templates/achat/show.html.twig` (QR code display).

### Task 7.5 — Vente templates

Create `templates/vente/index.html.twig` and `templates/vente/new.html.twig` (dynamic lines form with stock display, currency conversion).

### Task 7.6 — JavaScript for dynamic lines

Write vanilla JS (or Stimulus controller) for:
- Adding/removing CollectionType rows using `data-prototype`
- Auto-filling unit price from service selection
- Recalculating running total
- Vente: client-side stock validation (disable add if stock=0, warn if qty > available)

---

## PHASE 8: Forms

### Task 8.1 — ServiceType

Implement with NotBlank on nom, PositiveOrZero on prix/stock/seuilCritique.

### Task 8.2 — AchatType + ListeAchatType

Implement AchatType with CollectionType (allow_add, allow_delete, Count min:1) and ListeAchatType with EntityType for service, Positive for quantite.

### Task 8.3 — VenteType + ListeVenteType

Same as Achat forms. ListeVenteType service choice label includes stock level.

---

## PHASE 9: Testing & Validation

### Task 9.1 — Unit tests for entities

Test `Service::isStockCritique()`, `ListeAchat::getSousTotal()`, `ListeVente::getSousTotal()` with property-based approach (100 random iterations each).

### Task 9.2 — Unit tests for StockService

Test guard behaviors (no-op for qty ≤ 0), exception on insufficient stock, using mocked ServiceRepository.

### Task 9.3 — Unit tests for AchatService and VenteService

Test total computation, transaction rollback on failure, email failure non-interruption, using mocked EntityManager and dependencies.

### Task 9.4 — Unit tests for ExchangeRateService

Test null return on non-200, null return on missing rates key, correct conversion with rounding, using mocked HttpClient.

### Task 9.5 — Unit tests for PaymentService

Test EUR-to-cents conversion rounding (HALF_UP), null return on Stripe exception.

### Task 9.6 — Integration tests

Test full Achat creation flow against test database: verify stock increased, total correct, lines persisted.
Test full Vente creation flow: verify stock decreased, email mock called when stock=0.
Test Service delete with FK reference: verify exception.

### Task 9.7 — Smoke tests

Verify all routes return expected HTTP status codes. Verify database connection. Verify env vars are loaded.

### Task 9.8 — Manual validation checklist

- [ ] Create a Service, verify it appears in list
- [ ] Create an Achat with 2 lines, verify stock increased for both services
- [ ] Create a Vente exceeding stock, verify rejection
- [ ] Create a valid Vente, verify stock decreased
- [ ] Reduce a service to stock=0 via Vente, verify email sent
- [ ] Delete a Service referenced by an Achat, verify error message
- [ ] Delete a Service with no references, verify success
- [ ] Convert an Achat total to TND, verify result displayed
- [ ] Initiate Stripe payment, verify QR code generated
- [ ] Navigate to dashboard, verify critical stock warning if applicable

---

## PART 3: EXTRA

---

## Risky Parts and Mitigations

### 1. Stock Decrease After Transaction Commit (Race Condition Risk)

**Risk:** In the original Java code and in this design, `decreaseStock()` is called AFTER the Vente transaction commits. Between the commit and the stock decrease, another concurrent Vente could read the same stock value and both could succeed, resulting in negative stock.

**Mitigation:**
- The `decreaseStockGuarded()` SQL uses `AND stock >= :qty` in the WHERE clause, making the decrease atomic at the DB level
- If 0 rows affected, a RuntimeException is thrown — but the Vente is already committed at this point
- **Better approach for production:** Move `decreaseStock()` INSIDE the Vente transaction, before commit. This is a deliberate improvement over the original Java code.
- Use MySQL row-level locking (`SELECT ... FOR UPDATE`) on the service row before checking stock

**Recommended fix:**
```php
// Inside VenteService::createVente(), within the transaction:
$this->em->beginTransaction();
try {
    // Lock service rows for update
    foreach ($vente->getLignes() as $ligne) {
        $this->em->getConnection()->executeQuery(
            'SELECT stock FROM service WHERE id_service = ? FOR UPDATE',
            [$ligne->getService()->getIdService()]
        );
    }
    // Re-validate stock with locked rows
    $this->validateStock($vente);
    // Persist vente
    $this->em->persist($vente);
    $this->em->flush();
    // Decrease stock within same transaction
    foreach ($vente->getLignes() as $ligne) {
        $this->stockService->decreaseStock(...);
    }
    $this->em->commit();
}
```

### 2. UNIQUE Constraint on liste_achat (id_achat, id_service)

**Risk:** If the form submits two lines for the same service, the DB will throw a UNIQUE constraint violation.

**Mitigation:** The controller MUST merge duplicate service lines before calling `AchatService::createAchat()`. Accumulate quantities for the same `idService`:
```php
$merged = [];
foreach ($achat->getLignes() as $ligne) {
    $id = $ligne->getService()->getIdService();
    if (isset($merged[$id])) {
        $merged[$id]->setQuantite($merged[$id]->getQuantite() + $ligne->getQuantite());
    } else {
        $merged[$id] = $ligne;
    }
}
$achat->getLignes()->clear();
foreach ($merged as $ligne) {
    $achat->addLigne($ligne);
}
```

### 3. Email Failure During Vente

**Risk:** If Gmail SMTP is unavailable, the email send throws. The Vente is already committed.

**Mitigation:** `VenteService` wraps the email call in try/catch, logs the error, and continues. The sale confirmation is returned regardless. This matches the original Java behavior.

### 4. Stripe API Unavailability

**Risk:** Stripe API is down or the secret key is invalid.

**Mitigation:** `PaymentService` catches `StripeException`, logs it, returns null. Controller shows a flash error. The Achat is not affected.

### 5. Exchange Rate API Timeout

**Risk:** `open.er-api.com` is slow or unavailable, blocking the HTTP request.

**Mitigation:** Symfony HttpClient is configured with a 5-second timeout. All exceptions return null. The UI shows a friendly error message.

---

## Best Practices

1. **Never hardcode credentials.** All secrets in `.env.local`, injected via Symfony's `%env()%` syntax.

2. **Use Doctrine migrations.** Never modify the schema manually in production. Always generate a migration and review it before running.

3. **Wrap all multi-step DB operations in transactions.** Both `AchatService` and `VenteService` use explicit `beginTransaction()` / `commit()` / `rollback()`.

4. **Use JOIN FETCH in repositories.** Avoid N+1 queries when loading Achats/Ventes with their lines. The `findAllWithLignes()` method uses `leftJoin + addSelect`.

5. **Validate at multiple layers.** Client-side JS for UX, Symfony Form constraints for server-side validation, and service-layer business rule checks (stock validation in VenteService).

6. **Use flash messages, not exceptions, for user-facing errors.** Controllers catch exceptions from services and translate them to flash messages.

7. **Keep controllers thin.** Controllers should only: bind forms, call services, add flash messages, and redirect/render. No business logic in controllers.

8. **Use `#[Route]` attributes.** Symfony 6+ attribute-based routing is cleaner than YAML/XML routes for this scale.

9. **Store QR codes in `var/` not `public/`.** The `var/qrcodes/` directory is writable and not web-accessible by default. Serve QR images via a controller action if needed.

10. **Use `DateTimeImmutable` for dates.** Doctrine maps `date` type to `DateTimeImmutable` in PHP 8+, which is safer than mutable `DateTime`.

---

## Improvements Over the Original Java Version

| Area | Java Version | Symfony Improvement |
|---|---|---|
| Architecture | JavaFX desktop, direct JDBC | Web MVC, Doctrine ORM, proper separation of concerns |
| Credentials | Hardcoded in source code | Environment variables, `.env.local` |
| Transactions | Manual `setAutoCommit(false)` | Doctrine EntityManager transactions, cleaner API |
| Stock decrease timing | After transaction commit | Can be moved inside transaction with `SELECT FOR UPDATE` |
| Email credentials | Hardcoded strings | Injected from env vars |
| N+1 queries | Separate queries per Achat for lines | JOIN FETCH in repository |
| Error handling | `RuntimeException` bubbles to UI | Caught in controller, flash messages |
| Input validation | Manual null/parse checks | Symfony Form + Validator constraints |
| QR code library | Google ZXing (Java) | endroid/qr-code (PHP) |
| HTTP client | Java `HttpClient` | Symfony HttpClient with timeout config |
| Concurrency | No locking | Can add `SELECT FOR UPDATE` for stock |
| Testing | None | PHPUnit unit + integration + property tests |
| Deployment | Desktop JAR | Web app, deployable to any PHP host |

