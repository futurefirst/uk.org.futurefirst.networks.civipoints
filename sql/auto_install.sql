DROP TABLE IF EXISTS `civicrm_points`;

-- /*******************************************************
-- *
-- * civicrm_points
-- *
-- * Represents a grant of a certain number of points.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_points` (
  `id`                 int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Points ID',
  `contact_id`         int unsigned NOT NULL                COMMENT 'FK to Winning Contact',
  `grantor_contact_id` int unsigned DEFAULT NULL            COMMENT 'FK to Granting Contact',
  `points`             int          NOT NULL                COMMENT 'Number of points granted/removed',
  `grant_date_time`    datetime     NOT NULL                COMMENT 'Points granted at this date/time',
  `start_date`         date         NOT NULL                COMMENT 'Points effective from this date inclusive',
  `end_date`           date         DEFAULT NULL            COMMENT 'Points effective upto this date inclusive',
  `description`        varchar(255)                         COMMENT 'Description',
  `entity_table`       varchar(64)  DEFAULT NULL            COMMENT 'Points granted because of an entity of this type',
  `entity_id`          int unsigned DEFAULT NULL            COMMENT 'Points granted because of an entity with this ID',
  `points_type_id`     varchar(512) NOT NULL                COMMENT 'Option value for the type of points being granted',

  PRIMARY KEY (`id`),
  INDEX `index_date` (`start_date`, `end_date`),
  CONSTRAINT FK_civicrm_points_contact_id         FOREIGN KEY (`contact_id`)         REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_points_grantor_contact_id FOREIGN KEY (`grantor_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
