/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `career_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_goals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `short_term_goals` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `long_term_goals` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `industries_of_interest` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `career_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_goals_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `career_goals_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `career_officers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_officers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `officer_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_initial` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_main_officer` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_officers_officer_id_foreign` (`officer_id`),
  KEY `career_officers_institution_id_foreign` (`institution_id`),
  CONSTRAINT `career_officers_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_officers_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `career_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_opportunities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_opportunities_program_id_foreign` (`program_id`),
  CONSTRAINT `career_opportunities_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `division_codes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_sector_id_foreign` (`sector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `graduate_certification_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `graduate_certification_issuer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `graduate_certification_issue_date` date NOT NULL,
  `graduate_certification_expiry_date` date DEFAULT NULL,
  `graduate_certification_credential_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `graduate_certification_credential_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certifications_user_id_foreign` (`user_id`),
  CONSTRAINT `certifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_tel_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_mobile_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_street_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_brgy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_province` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_zip_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sector_id` bigint unsigned NOT NULL,
  `company_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_company_id_unique` (`company_id`),
  KEY `companies_user_id_foreign` (`user_id`),
  CONSTRAINT `companies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `widgets_configuration` json DEFAULT NULL,
  `recent_activities` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dashboards_user_id_foreign` (`user_id`),
  CONSTRAINT `dashboards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `degrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `degrees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Bachelor','Associate','Master','Doctoral','Diploma') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employment_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employment_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `job_types` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary_expectations` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_locations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `work_environment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `availability` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employment_preferences_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `employment_preferences_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_achievements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issuer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credential_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_achievements_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `graduate_achievements_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_certifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issuer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `credential_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_certifications_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `graduate_certifications_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_educations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_educations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `program` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_of_study` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_educations_graduate_id_foreign` (`graduate_id`),
  KEY `graduate_educations_institution_id_foreign` (`institution_id`),
  CONSTRAINT `graduate_educations_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduate_educations_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_experiences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `employment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_experiences_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `graduate_experiences_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_accomplishments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_projects_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `graduate_projects_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_skills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `skill_id` bigint unsigned NOT NULL,
  `proficiency_type` enum('Beginner','Intermediate','Advanced','Expert') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `years_experience` int NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_skills_graduate_id_foreign` (`graduate_id`),
  KEY `graduate_skills_skill_id_foreign` (`skill_id`),
  CONSTRAINT `graduate_skills_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduate_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduate_testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduate_testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `graduate_id` bigint unsigned NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduate_testimonials_graduate_id_foreign` (`graduate_id`),
  CONSTRAINT `graduate_testimonials_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `graduates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graduates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graduates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employment_status` enum('Employed','Underemployed','Unemployed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ethnicity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about_me` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `institution_id` bigint unsigned NOT NULL,
  `degree_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned NOT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personal_website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_social_links` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graduates_user_id_foreign` (`user_id`),
  KEY `graduates_institution_id_foreign` (`institution_id`),
  KEY `graduates_degree_id_foreign` (`degree_id`),
  KEY `graduates_program_id_foreign` (`program_id`),
  KEY `graduates_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `graduates_degree_id_foreign` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduates_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduates_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduates_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `human_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `human_resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_main_hr` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hr_user_id_foreign` (`user_id`),
  KEY `hr_company_id_foreign` (`company_id`),
  CONSTRAINT `hr_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hr_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institution_career_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institution_career_opportunities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `career_opportunity_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_career_opportunities_career_opportunity_id_foreign` (`career_opportunity_id`),
  KEY `institution_career_opportunities_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_career_opportunities_career_opportunity_id_foreign` FOREIGN KEY (`career_opportunity_id`) REFERENCES `career_opportunities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_career_opportunities_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institution_degrees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institution_degrees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `degree_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `degree_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_degrees_degree_id_foreign` (`degree_id`),
  KEY `institution_degrees_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_degrees_degree_id_foreign` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_degrees_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institution_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institution_programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `degree_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `program_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_programs_degree_id_foreign` (`degree_id`),
  KEY `institution_programs_program_id_foreign` (`program_id`),
  KEY `institution_programs_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_programs_degree_id_foreign` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_programs_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_programs_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institution_school_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institution_school_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_year_range_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_school_years_school_year_range_id_foreign` (`school_year_range_id`),
  KEY `institution_school_years_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_school_years_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_school_years_school_year_range_id_foreign` FOREIGN KEY (`school_year_range_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institution_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institution_skills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `skill_id` bigint unsigned NOT NULL,
  `career_opportunity_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_skills_skill_id_foreign` (`skill_id`),
  KEY `institution_skills_career_opportunity_id_foreign` (`career_opportunity_id`),
  KEY `institution_skills_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_skills_career_opportunity_id_foreign` FOREIGN KEY (`career_opportunity_id`) REFERENCES `career_opportunities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_skills_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `institution_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_president_first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_president_last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institutions_user_id_foreign` (`user_id`),
  CONSTRAINT `institutions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `job_id` bigint unsigned NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Applying' COMMENT 'Stage of the application process',
  `applied_at` timestamp NULL DEFAULT NULL,
  `interview_date` timestamp NULL DEFAULT NULL,
  `resume_id` bigint unsigned DEFAULT NULL,
  `cover_letter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `additional_documents` json DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_applications_user_id_foreign` (`user_id`),
  KEY `job_applications_job_id_foreign` (`job_id`),
  KEY `job_applications_resume_id_foreign` (`resume_id`),
  CONSTRAINT `job_applications_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_applications_resume_id_foreign` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `graduate_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_invitations_job_id_foreign` (`job_id`),
  KEY `job_invitations_graduate_id_foreign` (`graduate_id`),
  KEY `job_invitations_company_id_foreign` (`company_id`),
  CONSTRAINT `job_invitations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_invitations_graduate_id_foreign` FOREIGN KEY (`graduate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_invitations_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_program` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_program_job_id_foreign` (`job_id`),
  KEY `job_program_program_id_foreign` (`program_id`),
  CONSTRAINT `job_program_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_program_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `posted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vacancy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `experience_level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `skills` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sector_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `applicants_limit` int DEFAULT NULL COMMENT 'Limit the number of applicants for the job',
  `min_salary` int unsigned DEFAULT NULL,
  `max_salary` int unsigned DEFAULT NULL,
  `is_salary_negotiable` tinyint(1) NOT NULL DEFAULT '0',
  `program_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_sector_id_foreign` (`sector_id`),
  KEY `jobs_category_id_foreign` (`category_id`),
  KEY `jobs_company_id_foreign` (`company_id`),
  KEY `jobs_program_id_foreign` (`program_id`),
  CONSTRAINT `jobs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jobs_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jobs_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `next_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `next_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `availability` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locations` json DEFAULT NULL,
  `right_to_work` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary_expectation` json DEFAULT NULL,
  `sectors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `next_roles_user_id_foreign` (`user_id`),
  CONSTRAINT `next_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `peso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peso` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `peso_first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peso_last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peso_middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peso_user_id_foreign` (`user_id`),
  CONSTRAINT `peso_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `portfolios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'work_sample',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `portfolios_user_id_foreign` (`user_id`),
  CONSTRAINT `portfolios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `profile_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile_sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `personal_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `education` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `experience_level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `career_history` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `skills` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_sections_user_id_foreign` (`user_id`),
  CONSTRAINT `profile_sections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `degree_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `programs_degree_id_foreign` (`degree_id`),
  CONSTRAINT `programs_degree_id_foreign` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resumes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resumes_user_id_foreign` (`user_id`),
  CONSTRAINT `resumes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_range` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sectors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` int NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sector_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `division_codes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sectors_sector_code_unique` (`sector_code`),
  UNIQUE KEY `sectors_sector_id_unique` (`sector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `profile_visibility` tinyint(1) NOT NULL DEFAULT '1',
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `job_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `notification_preferences` json DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_user_id_foreign` (`user_id`),
  CONSTRAINT `settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `career_opportunity_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `skills_career_opportunity_id_foreign` (`career_opportunity_id`),
  CONSTRAINT `skills_career_opportunity_id_foreign` FOREIGN KEY (`career_opportunity_id`) REFERENCES `career_opportunities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `graduate_testimonials_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `graduate_testimonials_role_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `graduate_testimonials_testimonial` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `testimonials_user_id_foreign` (`user_id`),
  CONSTRAINT `testimonials_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `cover_photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_03_05_112612_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_03_05_112752_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_03_05_115054_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_03_07_152458_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_03_11_224907_add_user_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_03_11_230354_add_user_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_03_11_234521_add_user_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_03_12_000421_remove_email_columns_from_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_03_14_215924_add_is_approved_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_03_14_223312_make_columns_nullable_in_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_03_14_223604_make_columns_nullable_in_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_03_14_224232_change_graduate_year_graduated_to_integer_in_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_03_14_224812_change_graduate_year_graduated_to_integer_in_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_03_14_225950_change_graduate_year_graduated_to_integer_in_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_03_18_060420_add_user_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_03_18_071546_create_sectors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_03_18_080618_add_changes_to_sector_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_03_18_091906_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_03_18_095819_add_sector_id_to_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_03_18_210406_create_programs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_03_18_210537_create_graduates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_03_18_210720_create_institutions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_03_18_210753_create_career_opportunities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_03_18_210840_create_school_years_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_03_19_093456_add_archived_at_to_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_03_22_003700_add_is_approved_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_03_22_005914_add_is_approved_to_users_table2',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_03_22_073024_change_fields_in_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_03_27_033911_create_job_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_03_28_015000_create_portfolios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_03_28_015745_create_about_me_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_03_28_015817_create_profile_sections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_03_28_015932_create_resumes_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_03_28_015958_create_next_roles_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_03_28_020033_create_settings_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_03_28_020102_create_dashboards_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_03_28_031310_add_company_details_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_03_28_044702_create_career_opportunity_program_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_03_28_053337_make_company_street_address_nullable_in_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_03_28_054609_make_company_brgy_nullable_in_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_03_28_055115_make_company_city_nullable_in_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_03_28_055354_make_company_province_nullable_in_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_03_28_060734_make_company_zip_code_nullable_in_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_03_28_060943_make_company_email_nullable_in_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_03_28_075516_add_institution_name_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_03_28_204344_add_requirements_to_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_03_28_204551_add_sector_id_to_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_03_28_210008_add_category_id_to_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_03_29_001838_add_gender_dob_institution_contact_number_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_03_29_010939_add_company_hr_full_name_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_03_30_103809_add_salary_range_to_jobs_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_03_30_094328_add_user_id_to_about_me_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_03_30_135909_add_graduate_professional_title_to_users_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_04_01_133403_rename_company_columns_in_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_04_01_133740_add_graduate_middle_initial_to_users_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_04_01_140142_rename_columns_in_users_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_04_01_142748_change_graduate_year_graduated_to_string_in_users_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_04_01_151657_add_graduate_professional_title__to_users_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_04_02_020234_change_data_type_graduate_skills__to_users_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_04_02_031638_add_personal_summary__to_users_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_04_04_041621_add_deleted_at_to_sectors_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_04_04_050201_add_deleted_at_to_categories_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_04_04_073322_add_deleted_at_to_users_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_04_04_073844_add_deleted_at_to_users_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_04_04_073940_add_deleted_at_to_users_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_04_04_074447_add_deleted_at_to_users_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_04_04_003952_create_skills_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_04_04_064046_change_company_hr_contact_number',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_04_04_071630_change_compan_telephone_number',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_04_04_234943_create_educations_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_04_04_235023_create_experiences_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_04_04_235207_create_certifications_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_04_04_235255_create_achievements_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_04_04_235356_create_testimonials_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_04_04_235513_create_employment_preferences_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_04_04_235553_create_career_goals_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_04_05_011334_create_jobapplications_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_04_05_015018_edit_job_applications_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_04_05_025537_add_details_to_users_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_04_19_033138_edit_is_approved__to_users_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_04_19_034652_add_is_approved__to_jobs_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_04_19_081617_add_deleted_at__to_jobs_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_04_19_081617_add_deleted_at__to_jobs_table2',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_03_18_213556_create_degrees_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_03_18_218233_create_instiskills_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2025_04_07_145834_update_education_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2025_04_08_101621_create_projects_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2025_04_09_091822_update_graduate_education_end_date_column_type',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2025_04_09_102330_update_skills_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2025_04_09_120616_change_graduate_skills_proficiency_type_default_value',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2025_04_09_121124_update_graduate_skills_proficiency_type',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2025_04_09_162704_update_experiences_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2025_04_12_090253_remove_column_testimonials__table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2025_04_16_133923_change_columns_to_text_in_employment_preferences_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2025_04_22_172849_add_cover_photo_to_users_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2025_04_22_193908_add_description_to_users_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2025_04_23_010641_alter_description_column_in_jobs_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_04_23_044531_create_job_invitations_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_04_29_100859_add_branch_location_to_jobs_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2025_04_29_110642_add_salary_type_to_jobs_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2025_04_29_155843_add_attributes_to_jobs_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2025_04_29_212344_add_posted_by_to_jobs_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2025_04_30_074328_add_attributes_to_jobs_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2025_05_01_060224_add_company_name_and_main_hr_to_users',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2025_05_03_060944_add_company_name_column_to_jobs_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2025_05_09_013060_create_degrees_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2025_05_09_014537_create_programs_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2025_05_09_014550_create_school_years_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2025_05_09_015120_create_graduates_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2025_05_09_083431_add_verification_code_to_users_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2025_05_10_063310_remove_salary_type_and_job_benefits_in_jobs_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2025_05_10_065125_add_applicants_limit_in_jobs_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2025_05_12_042657_create_company_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2025_05_12_080754_create_companies_table',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2025_05_12_080837_configure_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2025_05_12_080837_configure_table copy',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2025_05_12_083239_configure_company_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2025_05_12_090332_configure_user_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2025_05_12_101707_add_company_id_to_user_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'0001_05_12_152607_create_career_opportunities_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'0002_05_12_152608_create_institution_career_opportunities_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2025_05_13_130429_add_salary_fields_to_jobs_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2025_05_13_134105_add_program_to_jobs_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2025_05_13_143558_rename_salary_columns_in_jobs_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2025_05_13_154151_create_job_program_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'0006_05_13_135410_create_skills',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'0007_05_13_135536_create_institution_skills_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'0009_05_15_043726_add_gender_and_dob_to_graduates',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2025_05_13_094426_add_company_name_to_jobs_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2025_05_15_065804_drop_programs_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2025_05_15_134938_create_job_applications_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2025_05_15_135734_add_interview_date_to_job_applications_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2025_05_15_140016_add_user_id_to_skills_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2025_05_15_143103_create_notifications_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2025_05_16_083202_add_profile_picture_to_users_table',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2025_05_16_091351_add_graduate_project_file_to_projects_table',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2025_05_16_120353_remove_company_name_in_jobs_table',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2025_05_16_235935_add_stage_column_in_job_applications_table',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2025_05_18_110314_create_hr_table',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'0002_05_18_202604_create_institutions_table',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'0004_05_18_202640_create_institution_school_years_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'0006_05_18_202659_create_institution_degrees_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'0008_05_18_202718_create_institution_programs_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2025_05_18_110629_add_sector_code_to__table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2025_05_18_111409_change_sector_id_type_in_sectors_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2025_05_18_121531_add_fields_categories__table',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2025_05_19_022428_add_company_id_in_companies_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'0003_05_18_202627_create_school_years_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'0009_05_18_202728_create_career_opportunities_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'0002_05_20_044613_create_institutions_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'0003_05_20_044652_create_school_years_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'0004_05_20_044723_create_institution_school_years_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'0005_05_20_044746_create_institution_degrees_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'0006_05_20_045633_create_institution_programs_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'0007_05_20_045725_create_career_opportunities_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'0008_05_20_044808_create_institution_career_opportunity_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'0009_05_20_044857_create_skills_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'0010_05_20_044916_create_institution_skills_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'0011_05_20_044954_create_peso_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'0012_05_20_044943_create_gradauates_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'0013_05_20_045059_create_career_officers_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'0014_05_20_045121_create_graduate_skills_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'0015_05_20_045128_create_graduate_education_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'0016_05_20_045142_create_graduate_experiences_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'0017_05_20_045158_create_graduate_projects_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'0018_05_20_045210_create_graduate_certifications_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'0019_05_20_045239_create_graduate_achievements_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'0020_05_20_045307_create_graduate_testimonials_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'0021_05_20_045317_create_graduate_preferences_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'0022_05_20_045327_create_graduate_career_goals_table',67);
