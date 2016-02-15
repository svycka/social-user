CREATE TABLE `social_user_providers` (
  `id`         INT(11)                    NOT NULL AUTO_INCREMENT,
  `localUser`  INT(11)                    NOT NULL,
  `identifier` VARCHAR(191)
               COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider`   VARCHAR(191)
               COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
