DROP TABLE `products`;
CREATE TABLE IF NOT EXISTS `products`
(
  `id`                     VARCHAR(256) NOT NULL,
  `ebay_id`                VARCHAR(256),
  `title`                  VARCHAR(256),
  `price`                  VARCHAR(256),
  `shipping`               VARCHAR(256),
  `category`               VARCHAR(256),
  `seller`                 VARCHAR(256),
  `watch`                  VARCHAR(256),
  `sold`                   VARCHAR(256),
  `imgs`                   TEXT,
  `specs`                  TEXT,
  `uri`                    TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;

DROP TABLE `compatibles`;
CREATE TABLE IF NOT EXISTS `compatibles`
(
  `id`                     VARCHAR(256) NOT NULL,
  `item_id`                VARCHAR(256) NOT NULL,
  `make`                   VARCHAR(256),
  `model`                  VARCHAR(256),
  `submodel`               VARCHAR(256),
  `year`                   VARCHAR(256),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;

DROP TABLE `loader_tasks`;
CREATE TABLE IF NOT EXISTS `loader_tasks`
(
  `id`                     VARCHAR(256) NOT NULL,
  `parser`                 VARCHAR(256) NOT NULL,
  `uri`                    TEXT,
  `created_at`             VARCHAR(256) NOT NULL,
  `updated_at`             VARCHAR(256) NOT NULL,
  `options`                TEXT,
  `status`                 VARCHAR(256) NOT NULL,
  `heartbeat_expiration`   VARCHAR(256) NOT NULL,
  `heartbeat_attempt`      VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;

DROP TABLE `parser_tasks`;
CREATE TABLE IF NOT EXISTS `parser_tasks`
(
  `id`                     VARCHAR(256) NOT NULL,
  `parser`                 VARCHAR(256) NOT NULL,
  `file`                   VARCHAR(256) NOT NULL,
  `created_at`             VARCHAR(256) NOT NULL,
  `updated_at`             VARCHAR(256) NOT NULL,
  `options`                TEXT,
  `status`                 VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;

DROP TABLE `proxies`;
CREATE TABLE IF NOT EXISTS `proxies`
(
  `id`                     VARCHAR(256) NOT NULL,
  `uri`                    VARCHAR(256) NOT NULL,
  `is_used`                VARCHAR(256) NOT NULL,
  `created_at`             VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;

DROP TABLE `sold_products`;
CREATE TABLE IF NOT EXISTS `sold_products`
(
  `id`                     VARCHAR(256) NOT NULL,
  `item_id`                VARCHAR(256),
  `uri`                    TEXT,
  `title`                  VARCHAR(256),
  `price`                  VARCHAR(256),
  `shipping`               VARCHAR(256),
  `seller`                 VARCHAR(256),
  `date`                   VARCHAR(256),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;
