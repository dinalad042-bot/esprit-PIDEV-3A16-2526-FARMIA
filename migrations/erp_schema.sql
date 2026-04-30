-- FarmIA Desk ERP — Run this SQL to create the ERP tables
-- Execute: php bin/console doctrine:migrations:diff && php bin/console doctrine:migrations:migrate

CREATE TABLE erp_service (
    id_service     INT AUTO_INCREMENT PRIMARY KEY,
    nom            VARCHAR(255) NOT NULL,
    description    LONGTEXT,
    prix           DOUBLE PRECISION NOT NULL DEFAULT 0,
    stock          INT NOT NULL DEFAULT 0,
    seuil_critique INT NOT NULL DEFAULT 0,
    INDEX idx_erp_service_stock (stock)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE erp_achat (
    id_achat   INT AUTO_INCREMENT PRIMARY KEY,
    date_achat DATE NOT NULL,
    total      DOUBLE PRECISION NOT NULL DEFAULT 0,
    INDEX idx_erp_achat_date (date_achat)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE erp_liste_achat (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_achat      INT NOT NULL,
    id_service    INT NOT NULL,
    quantite      INT NOT NULL,
    prix_unitaire DOUBLE PRECISION NOT NULL,
    CONSTRAINT fk_erp_la_achat   FOREIGN KEY (id_achat)   REFERENCES erp_achat(id_achat)     ON DELETE CASCADE,
    CONSTRAINT fk_erp_la_service FOREIGN KEY (id_service) REFERENCES erp_service(id_service) ON DELETE RESTRICT,
    UNIQUE KEY uk_erp_liste_achat (id_achat, id_service)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE erp_vente (
    id_vente   INT AUTO_INCREMENT PRIMARY KEY,
    date_vente DATE NOT NULL,
    total      DOUBLE PRECISION NOT NULL DEFAULT 0
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE erp_liste_vente (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_vente      INT NOT NULL,
    id_service    INT NOT NULL,
    quantite      INT NOT NULL,
    prix_unitaire DOUBLE PRECISION NOT NULL,
    CONSTRAINT fk_erp_lv_vente   FOREIGN KEY (id_vente)   REFERENCES erp_vente(id_vente)     ON DELETE CASCADE,
    CONSTRAINT fk_erp_lv_service FOREIGN KEY (id_service) REFERENCES erp_service(id_service) ON DELETE RESTRICT
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
