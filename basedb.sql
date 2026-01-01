-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 20, 2025 at 10:18 PM
-- Server version: 5.7.34
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `p2p_processing`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
                         `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                         `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
                               `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `callback_logs`
--

CREATE TABLE `callback_logs` (
                                 `id` bigint(20) UNSIGNED NOT NULL,
                                 `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `callbackable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `callbackable_id` bigint(20) UNSIGNED NOT NULL,
                                 `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `request_data` json DEFAULT NULL,
                                 `response_data` json DEFAULT NULL,
                                 `status_code` int(11) DEFAULT NULL,
                                 `is_success` tinyint(1) NOT NULL DEFAULT '0',
                                 `created_at` timestamp NULL DEFAULT NULL,
                                 `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
                              `id` bigint(20) UNSIGNED NOT NULL,
                              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `description` text COLLATE utf8mb4_unicode_ci,
                              `created_at` timestamp NULL DEFAULT NULL,
                              `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_merchant`
--

CREATE TABLE `category_merchant` (
                                     `id` bigint(20) UNSIGNED NOT NULL,
                                     `category_id` bigint(20) UNSIGNED NOT NULL,
                                     `merchant_id` bigint(20) UNSIGNED NOT NULL,
                                     `created_at` timestamp NULL DEFAULT NULL,
                                     `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
                            `id` bigint(20) UNSIGNED NOT NULL,
                            `receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `order_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `trader_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `created_at` timestamp NULL DEFAULT NULL,
                            `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
                               `id` bigint(20) UNSIGNED NOT NULL,
                               `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
                               `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
                               `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                               `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                               `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `funds_on_holds`
--

CREATE TABLE `funds_on_holds` (
                                  `id` bigint(20) UNSIGNED NOT NULL,
                                  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `source_wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
                                  `source_wallet_balance_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `destination_wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
                                  `destination_wallet_balance_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `holdable_id` bigint(20) UNSIGNED DEFAULT NULL,
                                  `holdable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `hold_until` timestamp NULL DEFAULT NULL,
                                  `created_at` timestamp NULL DEFAULT NULL,
                                  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
                            `id` bigint(20) UNSIGNED NOT NULL,
                            `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `network` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `tx_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `balance_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `created_at` timestamp NULL DEFAULT NULL,
                            `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
                        `id` bigint(20) UNSIGNED NOT NULL,
                        `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                        `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                        `attempts` tinyint(3) UNSIGNED NOT NULL,
                        `reserved_at` int(10) UNSIGNED DEFAULT NULL,
                        `available_at` int(10) UNSIGNED NOT NULL,
                        `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
                               `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `total_jobs` int(11) NOT NULL,
                               `pending_jobs` int(11) NOT NULL,
                               `failed_jobs` int(11) NOT NULL,
                               `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                               `options` mediumtext COLLATE utf8mb4_unicode_ci,
                               `cancelled_at` int(11) DEFAULT NULL,
                               `created_at` int(11) NOT NULL,
                               `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchants`
--

CREATE TABLE `merchants` (
                             `id` bigint(20) UNSIGNED NOT NULL,
                             `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `description` longtext COLLATE utf8mb4_unicode_ci,
                             `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `callback_url` longtext COLLATE utf8mb4_unicode_ci,
                             `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                             `active` tinyint(1) DEFAULT NULL,
                             `settings` longtext COLLATE utf8mb4_unicode_ci,
                             `gateway_settings` longtext COLLATE utf8mb4_unicode_ci,
                             `market` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `max_order_wait_time` int(10) UNSIGNED DEFAULT NULL,
                             `min_order_amounts` longtext COLLATE utf8mb4_unicode_ci,
                             `validated_at` timestamp NULL DEFAULT NULL,
                             `banned_at` timestamp NULL DEFAULT NULL,
                             `created_at` timestamp NULL DEFAULT NULL,
                             `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchant_api_request_logs`
--

CREATE TABLE `merchant_api_request_logs` (
                                             `id` bigint(20) UNSIGNED NOT NULL,
                                             `request_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `payment_gateway` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `payment_detail_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `request_data` json DEFAULT NULL,
                                             `response_data` json DEFAULT NULL,
                                             `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `execution_time` double DEFAULT NULL,
                                             `is_successful` tinyint(1) NOT NULL DEFAULT '0',
                                             `error_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `exception_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `exception_message` text COLLATE utf8mb4_unicode_ci,
                                             `merchant_id` bigint(20) UNSIGNED NOT NULL,
                                             `order_id` bigint(20) UNSIGNED DEFAULT NULL,
                                             `created_at` timestamp NULL DEFAULT NULL,
                                             `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchant_api_statistics`
--

CREATE TABLE `merchant_api_statistics` (
                                           `id` bigint(20) UNSIGNED NOT NULL,
                                           `date` date NOT NULL,
                                           `is_successful` tinyint(1) NOT NULL,
                                           `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `count` int(11) NOT NULL DEFAULT '0',
                                           `sum_amount` decimal(24,8) NOT NULL DEFAULT '0.00000000',
                                           `created_at` timestamp NULL DEFAULT NULL,
                                           `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchant_supports`
--

CREATE TABLE `merchant_supports` (
                                     `id` bigint(20) UNSIGNED NOT NULL,
                                     `merchant_id` bigint(20) UNSIGNED NOT NULL,
                                     `support_id` bigint(20) UNSIGNED NOT NULL,
                                     `created_at` timestamp NULL DEFAULT NULL,
                                     `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
                              `id` int(10) UNSIGNED NOT NULL,
                              `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
                                                          (1, '0001_01_01_000000_create_users_table', 1),
                                                          (2, '0001_01_01_000001_create_cache_table', 1),
                                                          (3, '0001_01_01_000002_create_jobs_table', 1),
                                                          (4, '2024_06_10_131438_create_permission_tables', 1),
                                                          (5, '2024_06_12_075236_create_payment_gateways_table', 1),
                                                          (6, '2024_06_13_112403_create_payment_details_table', 1),
                                                          (7, '2024_06_13_113428_create_orders_table', 1),
                                                          (8, '2024_06_16_115451_create_personal_access_tokens_table', 1),
                                                          (9, '2024_06_16_122803_create_sms_logs_table', 1),
                                                          (10, '2024_07_25_101808_create_sms_parsers_table', 1),
                                                          (11, '2024_08_16_122118_add_order_id_to_sms_logs_table', 1),
                                                          (12, '2024_08_16_132803_add_finished_at_to_orders_table', 1),
                                                          (13, '2024_08_17_134803_create_disputes_table', 1),
                                                          (14, '2024_09_02_201800_create_wallets_table', 1),
                                                          (15, '2024_09_02_210435_create_invoices_table', 1),
                                                          (16, '2024_09_12_051036_create_settings_table', 1),
                                                          (17, '2024_09_19_215353_create_transactions_table', 1),
                                                          (18, '2024_09_27_095534_create_telegrams_table', 1),
                                                          (19, '2024_09_28_124622_create_notifications_table', 1),
                                                          (20, '2024_10_23_163023_create_merchants_table', 1),
                                                          (21, '2024_10_25_170424_create_user_metas_table', 1),
                                                          (22, '2024_11_15_171305_add_is_h2h_to_orders_table', 1),
                                                          (23, '2024_11_26_014122_add_is_online_to_users_table', 1),
                                                          (24, '2024_11_26_234440_change_value_to_settings_table', 1),
                                                          (25, '2024_11_28_041037_add_logo_to_payment_gateways_table', 1),
                                                          (26, '2024_12_01_083503_add_settings_to_merchants_table', 1),
                                                          (27, '2024_12_01_115523_add_is_manually_to_orders_table', 1),
                                                          (28, '2024_12_14_021945_add_balance_type_to_transaction_table', 1),
                                                          (29, '2024_12_15_003021_change_source_type_to_balance_type_in_invoices_table', 1),
                                                          (30, '2024_12_20_075706_create_payout_gateways_table', 2),
                                                          (31, '2024_12_20_075720_create_payout_offers_table', 2),
                                                          (32, '2024_12_20_075839_create_payouts_table', 2),
                                                          (33, '2024_12_23_180446_add_columns_to_payment_gateways_table', 2),
                                                          (34, '2024_12_24_111534_add_commission_columns_to_user_metas_table', 2),
                                                          (35, '2024_12_27_141651_add_occupied_to_payout_offers_table', 2),
                                                          (36, '2024_12_28_154011_add_is_payout_online_to_users_table', 2),
                                                          (37, '2025_01_01_210358_create_funds_on_holds_table', 2),
                                                          (38, '2025_01_01_212405_add_refuse_column_to_payouts_table', 2),
                                                          (39, '2025_01_01_222151_add_video_receipt_to_payouts_table', 2),
                                                          (40, '2025_01_03_195424_add_cancel_reason_to_payouts_table', 2),
                                                          (41, '2025_01_07_043524_add_payouts_enabled_to_users_table', 3),
                                                          (42, '2025_01_07_050431_add_payout_reservation_time_to_payment_gateways', 3),
                                                          (43, '2025_01_07_220138_add_commission_balance_to_wallets_table', 3),
                                                          (44, '2025_01_21_144731_create_sender_stop_lists_table', 4),
                                                          (45, '2025_01_21_222756_change_message_on_sms_logs_table', 5),
                                                          (46, '2025_01_31_115000_add_parsing_result_to_sms_logs_table', 6),
                                                          (47, '2025_02_06_150039_add_amount_updates_history_to_orders_table', 7),
                                                          (48, '2025_02_09_211010_fix_gateway_settings', 8),
                                                          (49, '2025_02_11_221308_create_telescope_entries_table', 9),
                                                          (50, '2025_02_15_000221_add_max_pending_orders_quantity_to_payment_details_table', 9),
                                                          (51, '2025_02_15_003137_add_last_used_at_to_payment_details_table', 9),
                                                          (52, '2025_02_15_212235_add_market_to_merchants_table', 9),
                                                          (53, '2025_02_15_214850_add_market_to_orders_table', 9),
                                                          (54, '2025_02_16_045808_add_transaction_id_to_invoices_table', 9),
                                                          (55, '2025_02_17_041503_add_sub_status_to_orders_table', 9),
                                                          (56, '2025_02_18_062944_add_avatar_uuid_to_users_table', 10),
                                                          (57, '2025_02_19_064844_add_fix_last_used_at_at_payment_details_table', 10),
                                                          (58, '2025_02_20_093513_add_google2fa_secret_to_users_table', 11),
                                                          (59, '2025_02_20_153545_fix_avatars', 12),
                                                          (60, '2025_02_21_100213_add_schema_to_payment_gateways_table', 13),
                                                          (61, '2025_02_22_085225_add_columns_to_invoices_table', 14),
                                                          (62, '2025_02_25_094710_add_archived_at_to_payment_details_table', 15),
                                                          (63, '2025_02_26_132308_add_trader_id_to_disputes_table', 16),
                                                          (64, '2025_02_26_133202_add_trader_id_to_orders_table', 16),
                                                          (66, '2025_02_27_175710_remove_columns_form_user_metas_table', 18),
                                                          (67, '2025_02_27_175854_add_allowed_markets_to_user_metas_table', 18),
                                                          (68, '2025_02_28_133242_add_settings_to_merchants_table', 19),
                                                          (69, '2025_03_01_130551_change_commissions', 20),
                                                          (70, '2025_03_01_182658_add_columns_to_orders_table', 20),
                                                          (71, '2025_03_01_190551_change_merchant_settings', 20),
                                                          (72, '2024_03_03_add_user_device_id_to_payment_details', 21),
                                                          (73, '2024_03_03_add_user_device_id_to_sms_logs', 21),
                                                          (74, '2025_03_02_142957_rename_columns_to_orders_table', 21),
                                                          (75, '2025_03_02_142957_rename_columns_to_payment_gateways_table', 21),
                                                          (76, '2025_03_03_015843_create_user_login_histories_table', 21),
                                                          (77, '2025_03_03_140119_remove_service_commission_rate_from_orders_table', 21),
                                                          (78, '2025_03_03_140203_rename_service_commission_rate_total_to_orders_table', 21),
                                                          (79, '2025_03_03_151311_create_user_devices_table', 21),
                                                          (80, '2025_03_04_193904_create_merchant_api_request_logs_table', 21),
                                                          (81, '2025_03_04_223155_add_foreign_keys_to_tables', 21),
                                                          (82, '2025_03_07_000000_optimize_tables', 21),
                                                          (83, '2025_03_08_113226_update_user_devices_table', 22),
                                                          (84, '2025_03_16_222224_add_index_to_finished_at_on_orders_table', 23),
                                                          (85, '2025_03_16_223503_add_merchant_id_index_to_orders_table', 23),
                                                          (86, '2025_03_16_223548_add_indexes_to_disputes_table', 23),
                                                          (87, '2025_03_16_223612_add_is_online_index_to_users_table', 23),
                                                          (88, '2025_03_16_223627_add_indexes_to_payment_details_table', 23),
                                                          (89, '2025_03_17_011626_add_indexes_to_wallets_table', 23),
                                                          (90, '2025_03_17_015637_add_min_max_amount_to_payment_details_table', 23),
                                                          (91, '2025_03_17_021424_add_indexes_to_min_max_order_amount', 23),
                                                          (92, 'add_deal_interval_minutes_to_payment_details_table', 24),
                                                          (93, '2025_03_07_154950_create_promo_codes_table', 25),
                                                          (94, '2025_03_11_010939_create_categories_table', 25),
                                                          (95, '2025_03_11_010955_create_category_merchant_table', 25),
                                                          (96, '2025_03_11_140339_add_allowed_categories_to_user_metas_table', 25),
                                                          (97, '2025_03_11_145247_add_exception_fields_to_merchant_api_request_logs_table', 25),
                                                          (98, '2025_03_21_022353_create_payment_detail_payment_gateway_table', 25),
                                                          (99, '2025_03_21_022451_update_payment_gateway_id_in_payment_details', 25),
                                                          (100, '2025_03_21_022850_update_payment_gateway_id_in_orders', 25),
                                                          (101, '2025_03_21_022870_delete_payment_gateway_4', 25),
                                                          (102, '2025_03_21_022913_migrate_payment_gateway_relationships', 25),
                                                          (103, '2025_03_21_032716_remove_sub_payment_gateways_from_payment_gateways_table', 25),
                                                          (104, '2025_03_22_055219_remove_payment_gateway_id_from_payment_details', 25),
                                                          (105, '2025_03_22_055500_remove_legacy_payment_gateway_fields_from_payment_details', 25),
                                                          (106, '2025_03_24_153717_add_execution_time_to_merchant_api_request_logs_table', 25),
                                                          (107, '2025_03_24_155706_add_request_id_to_merchant_api_request_logs_table', 25),
                                                          (108, '2025_03_26_065001_update_merchants_market_from_garantex_to_bybit', 25),
                                                          (109, '2025_03_26_080305_add_promo_code_id_to_users_table', 25),
                                                          (110, '2025_03_26_081921_add_promo_used_at_to_users_table', 25),
                                                          (111, '2025_03_28_133906_add_is_intrabank_to_payment_gateways_table', 25),
                                                          (112, '2025_03_28_143016_create_user_notes_table', 25),
                                                          (113, '2025_03_28_145621_add_stop_traffic_to_users_table', 25),
                                                          (114, '2025_03_28_145933_add_stop_traffic_index_to_users_table', 25),
                                                          (115, '2025_03_28_151736_add_support_role', 25),
                                                          (116, '2025_03_29_105526_add_max_order_wait_time_to_merchants_table', 26),
                                                          (117, '2025_03_31_154848_add_traffic_enabled_at_to_users_table', 27),
                                                          (118, '2025_03_31_160910_set_default_traffic_enabled_at_for_users', 27),
                                                          (119, '2025_03_31_163141_create_sms_stop_words_table', 28),
                                                          (120, '2025_04_02_172007_add_is_vip_to_users_table', 29),
                                                          (121, '2025_04_03_184815_add_teamleader_balance_to_wallets_table', 30),
                                                          (122, '2025_04_03_185030_add_teamleader_balance_index_to_wallets_table', 30),
                                                          (123, '2025_04_03_192433_add_team_leader_role', 30),
                                                          (124, '2025_04_03_194436_add_referral_commission_percentage_to_users_table', 30),
                                                          (125, '2025_04_04_163237_add_team_leader_fields_to_orders_table', 30),
                                                          (126, '2025_04_06_194635_create_merchant_api_statistics_table', 30),
                                                          (127, '2025_04_11_002053_create_callback_logs_table', 30),
                                                          (128, '2025_04_18_111352_add_merchant_support_role', 30),
                                                          (129, '2025_04_18_113250_add_merchant_id_to_users_table', 30),
                                                          (130, '2025_04_26_105526_add_min_order_amounts_to_merchants_table', 30),
                                                          (131, '2024_05_24_create_merchant_supports_table', 31),
                                                          (132, '2025_05_24_174050_remove_payment_gateways_and_update_references', 32),
                                                          (134, '2025_09_16_000001_create_user_device_pings_table', 34),
                                                          (135, '2025_02_27_120157_create_pulse_tables', 35);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
                                         `permission_id` bigint(20) UNSIGNED NOT NULL,
                                         `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
                                   `role_id` bigint(20) UNSIGNED NOT NULL,
                                   `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
    (1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
                                 `id` bigint(20) UNSIGNED NOT NULL,
                                 `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `recipients_count` int(10) UNSIGNED NOT NULL,
                                 `delivered_count` int(10) UNSIGNED NOT NULL,
                                 `created_at` timestamp NULL DEFAULT NULL,
                                 `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
                          `id` bigint(20) UNSIGNED NOT NULL,
                          `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `base_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `total_profit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `trader_profit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `team_leader_profit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
                          `merchant_profit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `service_profit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `trader_paid_for_order` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `market` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `base_conversion_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `conversion_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `trader_commission_rate` float DEFAULT NULL,
                          `team_leader_commission_rate` float NOT NULL DEFAULT '0',
                          `total_service_commission_rate` float DEFAULT NULL,
                          `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `sub_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `callback_url` longtext COLLATE utf8mb4_unicode_ci,
                          `success_url` longtext COLLATE utf8mb4_unicode_ci,
                          `fail_url` longtext COLLATE utf8mb4_unicode_ci,
                          `amount_updates_history` longtext COLLATE utf8mb4_unicode_ci,
                          `is_h2h` tinyint(1) NOT NULL DEFAULT '0',
                          `is_manually` tinyint(1) DEFAULT '0',
                          `payment_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                          `old_payment_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                          `payment_detail_id` bigint(20) UNSIGNED DEFAULT NULL,
                          `trader_id` bigint(20) UNSIGNED DEFAULT NULL,
                          `team_leader_id` bigint(20) UNSIGNED DEFAULT NULL,
                          `finished_at` timestamp NULL DEFAULT NULL,
                          `merchant_id` bigint(20) UNSIGNED DEFAULT NULL,
                          `expires_at` timestamp NULL DEFAULT NULL,
                          `created_at` timestamp NULL DEFAULT NULL,
                          `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
                                         `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
                                   `id` bigint(20) UNSIGNED NOT NULL,
                                   `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `detail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `detail_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `initials` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `is_active` tinyint(1) NOT NULL DEFAULT '1',
                                   `daily_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `current_daily_limit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
                                   `max_pending_orders_quantity` int(10) UNSIGNED NOT NULL DEFAULT '1',
                                   `min_order_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `max_order_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `order_interval_minutes` int(10) UNSIGNED DEFAULT NULL,
                                   `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                                   `user_device_id` bigint(20) UNSIGNED DEFAULT NULL,
                                   `archived_at` timestamp NULL DEFAULT NULL,
                                   `last_used_at` timestamp NULL DEFAULT NULL,
                                   `created_at` timestamp NULL DEFAULT NULL,
                                   `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_detail_payment_gateway`
--

CREATE TABLE `payment_detail_payment_gateway` (
                                                  `id` bigint(20) UNSIGNED NOT NULL,
                                                  `payment_detail_id` bigint(20) UNSIGNED NOT NULL,
                                                  `payment_gateway_id` bigint(20) UNSIGNED NOT NULL,
                                                  `created_at` timestamp NULL DEFAULT NULL,
                                                  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
                                    `id` bigint(20) UNSIGNED NOT NULL,
                                    `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `nspk_schema` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `min_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `max_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    `sms_senders` longtext COLLATE utf8mb4_unicode_ci,
                                    `commission_rate` float DEFAULT NULL,
                                    `service_commission_rate` float DEFAULT '9',
                                    `trader_commission_rate_for_orders` float NOT NULL DEFAULT '2.5',
                                    `trader_commission_rate_for_payouts` float NOT NULL DEFAULT '2.5',
                                    `total_service_commission_rate_for_orders` float NOT NULL DEFAULT '9',
                                    `total_service_commission_rate_for_payouts` float NOT NULL DEFAULT '9',
                                    `is_active` tinyint(1) NOT NULL DEFAULT '1',
                                    `is_intrabank` tinyint(1) NOT NULL DEFAULT '0',
                                    `detail_types` longtext COLLATE utf8mb4_unicode_ci,
                                    `reservation_time_for_orders` int(10) UNSIGNED NOT NULL DEFAULT '10',
                                    `reservation_time_for_payouts` int(10) UNSIGNED NOT NULL DEFAULT '10',
                                    `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `code`, `nspk_schema`, `currency`, `min_limit`, `max_limit`, `sms_senders`, `commission_rate`, `service_commission_rate`, `trader_commission_rate_for_orders`, `trader_commission_rate_for_payouts`, `total_service_commission_rate_for_orders`, `total_service_commission_rate_for_payouts`, `is_active`, `is_intrabank`, `detail_types`, `reservation_time_for_orders`, `reservation_time_for_payouts`, `logo`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (1, 'Сбербанк', 'sberbank', '100000000111', 'rub', '1000', '500000', '[\"900\",\"ru.sberbankmobile\"]', 2.5, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"account_number\",\"phone\"]', 20, 10, 'logo_r1pnnbviybidoycnbxlrvi7freyekear.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (2, 'Альфа-Банк', 'alfabank', '100000000008', 'rub', '1000', '500000', '[\"ru.alfabank.mobile.android\",\"alfa-bank\",\"ru.alfabank.mobile.android.huawei\",\"ru.alfabank.oavdo.amc\"]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_z1fvup0xc3c4benbihrjexnuvb6lnedk.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (3, 'Райффайзенбанк', 'raiffeisenbank', '100000000007', 'rub', '1000', '100000', '[\"ru.raiffeisennews\"]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_bf1akiluzkoebzzzm6gnhfvmktamakp3.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (5, 'Halyk', 'halyk_kzt', NULL, 'kzt', '1000', '5000000', '[]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_2jganiql8qyzsoigpfxaj3ymgwuxl88m.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (6, 'Jusan', 'jusan_kzt', '103004000111', 'kzt', '1000', '100000', '[\"kz.tsb.app24\"]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\"]', 20, 10, 'logo_tfvitkcjddiilqojo4jrilhhlx1ggqp8.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (7, 'Eurasian', 'eurasian_kzt', NULL, 'kzt', '1000', '100000', '[]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\"]', 20, 10, 'logo_tnszyacbs2kl8wosiqqoiiizj2hnjgww.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (8, 'ОТП', 'otp_rub', '100000000018', 'rub', '1000', '100000', '[\"ru.otpbank.mobile\",\"otp_bank\",\"OTP Bank\"]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_6ori1bdsczzxvsbg8c9pp5tzpvve72uw.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (9, 'ЮMoney', 'yoomoney', '100000000022', 'rub', '1000', '100000', '[\"ru.yoo.money\"]', 2.5, 9, 7, 2.5, 10, 9, 1, 0, '[\"card\"]', 20, 15, 'logo_i1gjihtug8lfhip9xn8tewkcajkqmoer.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (10, 'МТС Банк', 'mts', '100000000017', 'rub', '1100', '100000', '[\"MTS-Bank\",\"ru.mts.bank\"]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_i9kksxu9eilzuw1ikem6nyxvcywo74xy.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (11, 'ДОМ.РФ', 'domrfbank', '100000000082', 'rub', '1000', '100000', '[\"bank_dom.rf\"]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\"]', 20, 15, 'logo_h1oztggbsncthdycqkzqhz0q4yt4ctey.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (12, 'Росбанк', 'ros_bank', '100000000212', 'rub', '1000', '100000', '[]', 2.5, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\"]', 20, 10, 'logo_lfdov64xxglotpvmwcv9me9xa1n3gbsd.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (13, 'ФораБанк', 'fora', '100000000217', 'rub', '1000', '500000', '[\"ru.briginvest.sense\",\"fora-bank\"]', 8, 1, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_y2ar02pspwjx1ytwzjpayje0nxuxglqj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (14, 'Тинькофф', 'tinkoff', '100000000004', 'rub', '1000', '500000', '[\"tinkoff\",\"\\u0422-\\u0411\\u0430\\u043d\\u043a\",\"com.idamob.tinkoff.android\",\"t-bank\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_qcpydg51dlbkmiaq3jcq6glytf3a0wzy.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (15, 'ВТБ', 'vtb', '110000000005', 'rub', '1000', '500000', '[\"vtb\",\"VTB\",\"ru.vtb24.mobilebanking.android\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_n58agwcajzp45bgmlggsrrvfm39ifmaf.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (16, 'МКБ', 'mkb', '100000000025', 'rub', '1000', '500000', '[\"ru.mkb.mobile\",\"mkb\"]', NULL, 9, 7, 2.5, 10, 1, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_kiex3umkwbeltfdmikcxcqcu7rx7atch.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (17, 'РСХБ', 'rosselhozbank', '100000000020', 'rub', '1000', '500000', '[\"rshb\",\"ru.rshb.dbo\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_dotolxx5wuma72ey7xnuy0ar3okd1l2c.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (18, 'Газпром', 'gazprombank', '100000000001', 'rub', '1000', '500000', '[\"Gazprombank\",\"ru.gazprombank.android.mobilebank.app\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_mxrxzg8fzr9iuydcawbo7clc8zvefeec.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (19, 'Wildberries', 'wb_rub', '100000000259', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 15, 10, 'logo_soswvenj0szhemj1uauf2aspg2a2uz1q.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (20, 'Почта Банк', 'pochta', '100000000016', 'rub', '1000', '500000', '[\"ru.letobank.prometheus\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_wkg5mvc4qx57eoh725yjvf5b4esdpz1p.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (21, 'OZON Банк', 'ozon', '100000000273', 'rub', '1000', '500000', '[\"ru.ozon.app.android\",\"ru.ozon.fintech.finance\",\"OzonFinance\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_auqhrifa5wtilw1tgay6eqraeummnhwq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (22, 'Кубань', 'kkbank', '100000000050', 'rub', '1000', '500000', '[\"KubanKredit\",\"ru.kubankredit.testproject1\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"account_number\"]', 20, 10, 'logo_wy64x5ghuysfyradnohttzfw3zrwbqnx.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (23, 'Юнистрим', 'unistream', '100000000042', 'rub', '1000', '500000', '[\"com.ltech.unistream\",\"unistream\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_jgyjljzhsejwuqsxl4xzijzokyfgzkyq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (24, 'Совком Банк', 'sovkom', '100000000013', 'rub', '1000', '500000', '[\"ru.sovcomcard.halva.v1\",\"sovcombank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 15, 'logo_44zdrgwvfho2w3dmyrwvhurevjs6lkwk.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (25, 'Промсвязьбанк', 'promsvyaz', '100000000010', 'rub', '1000', '500000', '[\"PSB\",\"logo.com.mbanking\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_wvsdso8zlyymvvcqjpzdkiopwecfs26e.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (26, 'Ак Барс', 'ak_bars_bank', '100000000006', 'rub', '1200', '500000', '[\"ru.akbars.mobile\",\"akbars\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 12, 'logo_ihgjcdaaiv3lirpxzwa9qx0u2vf0z1sz.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (27, 'МТС Деньги', 'mtsdengi_rub', '100000000289', 'rub', '1000', '500000', '[\"MTS.dengi\",\"ru.lewis.dbo\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 15, 'logo_kcb2aghpxnllinvyxuare93wh8u12usl.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (28, 'Зенит Банк', 'zenit', '100000000045', 'rub', '1000', '500000', '[\"ru.zenitonline.android\",\"bankzenit\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_v8s9ennc0vi47hsg9lp0tcmw9d4zcygb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (29, 'Абсолют Банк', 'absolute_bank', '100000000047', 'rub', '1000', '500000', '[\"ru.ftc.faktura.absolutbank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_te4d6lsubn4yzoyvzbigjmqw4ilkbquj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (30, 'Азиатско-Тихоокеанский Банк', 'aziatsko-tihookeanskij-bank', '100000000108', 'rub', '1000', '500000', '[\"su.atb.mobileapp\",\"atb\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_ppj8q0drgn2akrh5e7wzl5tufxymmaeq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (31, 'Приморье Банк', 'primbank', '100000000226', 'rub', '1000', '500000', '[\"ru.ftc.faktura.multibank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_j5wlvazy8tfccq6hszldgta7r7a3vayh.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (32, 'Левобережный Банк', 'nskbl', '100000000052', 'rub', '1000', '500000', '[\"ru.ftc.faktura.nskbl\",\"nskbl.ru\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_kbkmmnd8mrkdwruu4zxvkaszmesw9z3p.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (33, 'Ингосстрах Банк', 'ingo', '100000000078', 'rub', '1000', '500000', '[\"com.banksoyuz.artsofte\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_mh3bwyzs1uaieemqjjr45o7thpr3gdyx.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (34, 'ББР Банк', 'bbr-bank', '100000000133', 'rub', '1000', '500000', '[\"com.bifit.mobile.private.bbr\",\"bbr_bank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_3crutb38uhdyufewg6lloyrhediayund.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (35, 'Синара Банк', 'bank-sinara', '100000000003', 'rub', '1000', '500000', '[\"ru.skbbank.ib\",\"bank-sinara\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_pqpqwmsih2qifokvj7itaw08af2uwmd5.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (36, 'Ренессанс Банк', 'rencredit', '100000000032', 'rub', '1000', '500000', '[\"\\u0420\\u0435\\u043d\\u0435\\u0441\\u0441\\u0430\\u043d\\u0441\",\"Rencredit\",\"cz.bsc.rc\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_slqa7amtbk5jh0bi7xfoci43rrbjl9l1.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (37, 'Солид Банк', 'solid-bank', '100000000230', 'rub', '1000', '500000', '[\"ru.ftc.faktura.solidbank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_48pgeyffi8vqe8wymcib7z7um3tjqoke.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (38, 'РНКБ Банк', 'rncb', '100000000011', 'rub', '1000', '500000', '[\"com.bifit.rncbbeta\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_2b13orvnuijfxnhpu21ogaks1m0n1amg.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (39, 'УБРиР Банк', 'ubrib', '100000000031', 'rub', '1000', '500000', '[\"UBRR\",\"cb.ibank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_4p0wpff35oago3nynfr4iqyhei9reusl.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (40, 'Уралсиб банк', 'uralsib', '100000000026', 'rub', '1000', '500000', '[\"ru.bankuralsib.mb.android\",\"uralsib\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_awwdajddt3eqnkgyzcz0jcwdj3oaogm3.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (41, 'Солидарность Банк', 'solid', '100000000121', 'rub', '1000', '500000', '[\"com.isimplelab.ibank.solidarnost\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_ufyatyqipixybhshpaafrmyqbgefh48t.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (42, 'БКС банк', 'bksbank', '100000000041', 'rub', '1000', '500000', '[\"ru.bcs.bcsbank\",\"bcsbank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_dokcdlmwjfoedolvyhhpxrtzhan6axty.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (43, 'Акибанк', 'akibank', '100000000107', 'rub', '1000', '500000', '[\"ru.ftc.faktura.akibank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_wsudfzas3pnz8kb5k7z4mvx4vhwtgiur.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (44, 'Интеза Банк', 'bancaintesa', '100000000170', 'rub', '1000', '500000', '[\"ru.ftc.faktura.intesabank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_63xocxmxrb6jmfabfpcwxhtzarquygsl.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (45, 'Кредит Европа Банк', 'crediteurope', '100000000027', 'rub', '1000', '500000', '[\"com.idamobile.android.crediteuropa\",\"c.e.bank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_ezsy3ko8tpbeg0mxidgdxletb43gtijv.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (46, 'ТКБ Банк', 'tkbbank', '100000000034', 'rub', '1000', '500000', '[\"ru.ftc.faktura.tkbbank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_bz1yzpnihhsdxgfxibsocjs02tbvu82j.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (47, 'Просто Банк', 'posto_rub', '105507700018', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_2oftukivrj9deu6anxz5azwdsjl25vbj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (48, 'Свой Банк', 'svoi', '10000000888', 'rub', '1000', '500000', '[\"Svoibank\",\"aosvoibank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_mlc3dgalgnmq6k5npdjk5dwf6mm9klkj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (49, 'БыстроБанк', 'bystrobank', '100000000092', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_pqegvynexdlxhijzpr0d7dmjobyrnzbh.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (50, 'ЮниКредит Банк', 'unicreditbank', '100000000030', 'rub', '1000', '500000', '[\"ru.unicredit.android\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_al88mufehokuhbwzynqmcut7i2ayjawp.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (51, 'ПромТрансБанк', 'prome_rub', '198800000273', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_8jozgv6txtz7zhbur8f0bdcizovgzsm4.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (52, 'Драйв Клик', 'drajv-klik-bank', '100000000250', 'rub', '1000', '500000', '[\"com.cetelem.cetelem_android\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_z4j4kbfwe5n3fqdqzrqrjxdofendgc41.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (53, 'Форштадт Банк', 'forshtadt', '100000000081', 'rub', '1000', '500000', '[\"ru.ftc.faktura.forshtadt\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_lwludbcgkvycmw0paaji6i7v6ar4v9uh.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (54, 'ВБРР Банк', 'vbrr', '100000000049', 'rub', '1000', '500000', '[\"com.bssys.vbrrretail\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_iorxktdfk9omdpbhsz67bxy01i5ajlqc.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (55, 'Новикомбанк', 'novikom', '100000000177', 'rub', '1000', '500000', '[\"com.bssys.novikomretail\",\"novikom\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_lw2mfn67ezxggjzcvh3xogq0bfftyb7b.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (56, 'Металлинвестбанк', 'metallinvestbank', '100000000046', 'rub', '1000', '500000', '[\"METIB_CARD\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_vf6v1tjlfvfbbzywarqj3bw5g76sdyn4.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (57, 'Авангард', 'avangard', '100000000028', 'rub', '1000', '500000', '[\"avangard\",\"ru.avangard\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_z4ogsuvwrcdhbw0beecqxiz8wcgokeqk.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (58, 'Экспобанк', 'expobank', '100000000044', 'rub', '1000', '500000', '[\"ru.ftc.faktura.expobank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_douhncs4zywkpkkzbusesm4sqqdgzeca.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (59, 'Финам Банк', 'finam', '100000000040', 'rub', '1000', '500000', '[\"ru.finambank.app\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_bmais3jvkiptlahf4y7batwg9gbkmccc.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (60, 'МСП Банк', 'mspbank', '100000000999', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_blzaurs3kc4akmpodbdd8ordsexmkmvu.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (61, 'Цифра Банк', 'cifra', '100000000265', 'rub', '1000', '500000', '[\"com.bankffin.portfolio\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_c5unfotfdvhx4yx9wixcuq48dfooc6dq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (62, 'Энергобанк', 'energobank', '100000000159', 'rub', '1000', '500000', '[\"com.energobank.digital\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_zta027xq5fatqu3dm9tzoidzj3b6khh4.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (63, 'Реалист Банк', 'realistbank', '100000000232', 'rub', '1000', '500000', '[\"ru.ftc.faktura.baikalinvestbank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_ak7nr77bmaidw2is3jo2naawyxezsvg7.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (64, 'Локо Банк', 'lockobank', '100000000161', 'rub', '1000', '500000', '[\"com.idamobile.android.LockoBank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_bd4pfhowjzjlrmqmzg01gijzjccjeg82.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (65, 'Яндекс Банк', 'jandeks-bank', '100000000150', 'rub', '1000', '500000', '[\"\\u042f\\u043d\\u0434\\u0435\\u043a\\u0441 \\u041f\\u0435\\u0439\",\"\\u042f\\u043d\\u0434\\u0435\\u043a\\u0441 \\u0411\\u0430\\u043d\\u043a\",\"com.yandex.bank\"]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_9twks6wg7mb34qzntzpa2hub9robakpe.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (66, 'Вологжанин Банк', 'bank-vologzhanin', '100000000888', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2.5, 10, 1.5, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_pqk0gyu58e81xwsueqlttpjhzkaywdyy.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (67, 'Генбанк', 'genbank', '100000000037', 'rub', '1000', '500000', '[\"genbank\",\"com.mmonline.mobile\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_9jjbothwcmfoch9donz0vvi5u70oxtys.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (68, 'ТомскПромСтройБанк', 'tpsbank', '100000000206', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_ssexozygbgm84epf0peinmaluyuu9syb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (69, 'Kaspi', 'kaspi_kzt', NULL, 'kzt', '1000', '5000000', '[\"kz.kaspi.mobile\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_3qztdzcrvgpm503oygfgriddck5mv7od.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (70, 'Home Credit Bank', 'homecreditbank_kzt', '100001100418', 'kzt', '1000', '5000000', '[\"home.kz\",\"kz.home.capp\",\"kz.kkb.homebank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_xhhoa3hh4juzqiemmfmra4b95czdhcms.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (71, 'Контур Банк', 'kontur_rub', NULL, 'rub', '1500', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 10, 'logo_yqinvmzyov7pwjfq5xmc0b00td1dy0kl.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (72, 'Freedom bank', 'freedom_kzt', '100000330111', 'kzt', '10000', '10000000', '[\"ffinbank.myfreedom\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\"]', 20, 15, 'logo_nlh6fan3ibwd9wixritwjbiz7zuciask.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (73, 'Русский Стандарт', 'rsb', '100000000014', 'rub', '1000', '500000', '[\"ru.simpls.brs2.mobbank\",\"rsb.ru\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_q9vwnhqm4zsfmqqftznptzdeqc9srjp6.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (74, 'Точка Банк', 'tochka-bank', '100000000284', 'rub', '1000', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_wuf3yb5dzxgkdmv0nhs2iaykxzbkzugb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (75, 'Тимер Банк', 'timerbank', '100000000144', 'rub', '1000', '500000', '[\"com.timerbank.retail\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_wfvt3vd7t2w2jb05lhlvuqu12sjmafg3.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (76, 'Долинск Банк', 'kb-dolinsk', '100000000270', 'rub', '1000', '500000', '[\"ru.ftc.faktura.dolinsk\",\"BankDolinsk\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_9vuzrrl92kkgillsu7ky0hovqp85vaq4.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (77, 'Таджикистан Амонатбанк', 'amonatbank_rub', NULL, 'rub', '50', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_fpqqjgb3agay2ztpcnjsciahavxkkhie.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (78, 'Таджикистан Тавхидбанк', 'tavhidbank_rub', NULL, 'rub', '100', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\"]', 20, 15, 'logo_mwmly5dzqew77exwlpm3ohdpgxqumg15.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (79, 'Таджикистан Банк Арванд', 'arvard_rub', NULL, 'rub', '100', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_qje8wj9laasyprw8o3omlebvpmp2obk3.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (80, 'Таджикистан Банк Душанбе Сити', 'dushambecity_rub', NULL, 'rub', '50', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_gxf78e5fasfn5kmcgciy0udamnog9p7f.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (81, 'Таджикистан Банк Эсхата', 'esxata_rub', NULL, 'rub', '50', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_bctnsdrrjvdsgcgn2e1s9mkvl1hx0x9w.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (82, 'Таджикистан Банк Спитамен', 'spitamen_rub', NULL, 'rub', '50', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 20, 'logo_nzoeexh68ltfh058qxybojwyq1ojnigu.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (83, 'Таджикистан Банк Алиф', 'alif_rub', NULL, 'rub', '50', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_kpzwll4utbi0cieq9owryvezv2atnm8i.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (84, 'Таджикистан Международный банк', 'mbtjs_rub', NULL, 'rub', '50', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_gpxx4iblsnzq16vegnfmn7fhmf6bvcpn.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (85, 'Абхазия А-Мобаил кошелек', 'amobile_rub', NULL, 'rub', '1000', '500000', '[\"com.amobile.application\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 15, 'logo_ijnsyqefd2hkbqp6lounvndmrs4zz71b.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (86, 'АБ Россия', 'abr', '100000000095', 'rub', '1000', '500000', '[\"abr\",\"ru.artsofte.russiafl\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 15, 'logo_vvbimkfzcrgnbax9olbzfwzn2jikrlsi.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (87, 'Банк Санкт-Петербург', 'bspb', '100000000029', 'rub', '1000', '500000', '[\"ru.bspb\",\"BankSPB\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_lscbkygkifj7hn7ezuiydouygkcrj9i7.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (88, 'Челябинвестбанк', 'chelinvest', '100000000094', 'rub', '1000', '500000', '[\"ru.chelyabinvestbank.investpay\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_kaouyijw5ntfz0mvdtbe8flrcoytnuzm.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (89, 'Цупис', 'cupis_rub', NULL, 'rub', '700', '500000', '[]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\"]', 20, 12, 'logo_8le8pbm3zqltmaibd739phzmfj9aztxi.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (90, 'Норвик Банк', 'norvikbank', '100000000202', 'rub', '1000', '500000', '[\"ru.vtkbank.android\",\"Norvik_Bank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 15, 'logo_uyoaj5omgneq9trnjlc0yc2a4zplxfzb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (91, 'Хлынов Банк', 'bank-hlynov', '100000000056', 'rub', '1000', '500000', '[\"ru.bank_hlynov.xbank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 15, 'logo_kgmsax8nlcr5mxal3klud9lt0azhwte0.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (92, 'Первый ДорТранс Банк', 'dtb1', '100000000174', 'rub', '1000', '500000', '[\"ru.ftc.faktura.finbank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 15, 'logo_3ltgkp1bwiryhb1b9yraxdgqonx5wt3c.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (93, 'Газэнергобанк', 'gebank', '100000000043', 'rub', '1000', '500000', '[\"ru.gebank.ib\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_glmwk9qgfj6ugfqnmj9denzdnjn8oqsa.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (94, 'Бланк банк', 'blanc', '100000000053', 'rub', '1000', '500000', '[\"ru.ftc.faktura.vesta\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_yfatqcw7cykpen1b6v8jvrgsgcslhkqj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (95, 'Хакасский муниципальный банк', 'kbhmb', '100000000127', 'rub', '1000', '500000', '[\"ru.ftc.faktura.kbhmb\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 15, 'logo_2q4pq1jyobrosdqcm5xhpkenpnjw4fqb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (96, 'ПСКБ', 'pscb', '100000000087', 'rub', '1000', '500000', '[\"ru.ftc.faktura.pskb\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_oh2tjfqs34peoff23yxcndawy5prxsig.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (97, 'Кошелев банк', 'koshelev-bank', '100000000146', 'rub', '1000', '500000', '[\"com.bifit.mobile.citizen.kbnk\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_0kkbse8gzfmoz1hbxt1hmelkasi6d7dk.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (98, 'СДМ-Банк', 'sdm', '100000000069', 'rub', '1000', '500000', '[\"ru.ftc.faktura.sdm\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_ldkd1m3ysdequk7l6egvuthq4tdfyx9s.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (99, 'Акцепт Банк', 'akcept', '100000000135', 'rub', '1000', '500000', '[\"ru.ftc.faktura.akcept\",\"BankAkcept\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_230cetvkamzwcrbzmnlxymizt9cby3px.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (100, 'Газтрансбанк', 'gaztransbank', '100000000183', 'rub', '1000', '500000', '[\"ru.ftc.faktura.gaztransbank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_kvxg1l60ny04adaib8tfbn6vudx2ozhw.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (101, 'ЧЕЛИНДБАНК', 'chelindbank', '100000000106', 'rub', '1000', '500000', '[\"com.isimplelab.ibank.chelind\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 10, 'logo_lns2cdfkg7petzdek9z20ngalum0ogxe.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (102, 'МФК Банк', 'mezhdunarodnyj-finansovyj-klub', '100000000203', 'rub', '1000', '500000', '[\"ru.ftc.faktura.mfkbank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_gpl2gejg8vr4tzrnouzef7asotxcxkah.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (103, 'Александровский Банк', 'bank-aleksandrovskij', '100000000211', 'rub', '1000', '500000', '[\"ru.ftc.faktura.alexbank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_nom0xdhpip4bhqj81kzft7wezutd6hgr.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (104, 'Центр-инвест', 'centrinvest', '100000000059', 'rub', '1000', '500000', '[\"ru.centrinvest.mobilebanking2018\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_gf5e7xgdiosfjcewbvvmdmvtmmcb1c3a.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (105, 'РостФинанс', 'rost_finance', '100000000098', 'rub', '1000', '500000', '[\"ru.ftc.faktura.rostfinance\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\"]', 20, 15, 'logo_3rg7g0tvbim8lovnedlhul3b3ktmfx5l.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (106, 'Кубаньторгбанк', 'bktb', '100000000180', 'rub', '1000', '500000', '[\"ru.isfront.android.kt\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_dabpgd8pwo63kd6pcftswvpk4gpokefr.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (107, 'Заречье', 'zarech', '100000000205', 'rub', '1000', '500000', '[\"com.bifit.mobile.citizen.zarech\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_sqhygty5bpvzsnafjbrl93ngzh69iqqm.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (108, 'Банк Кремлевский', 'kremlinbank', '100000000201', 'rub', '1000', '500000', '[\"ru.ftc.faktura.kremlevskiy\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_anwyithqhvzhe6a1tsn3n4xcmsshuguo.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (109, 'Морской Банк', 'maritimebank', '100000000171', 'rub', '1000', '500000', '[\"ru.ftc.faktura.maritimebank\"]', NULL, 9, 7, 2, 10, 2, 1, 0, '[\"card\",\"phone\"]', 20, 15, 'logo_cckurvp3u9xurgmcnpcabl35a9z4lprx.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (110, 'Углемет', 'coalmetbank', '100000000093', 'rub', '1000', '500000', '[\"com.isimplelab.isimpleceo.uglemet\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_8xyicukhezmohizjf8aqx4jttdkilcg8.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (111, 'Дальневосточный банк', 'dvbank', '100000000083', 'rub', '1000', '500000', '[\"com.bifit.dvbank\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\"]', 20, 15, 'logo_rl9uv9xirff4aldadksqjwsoireqvtpy.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (112, 'Датабанк', 'databank', '100000000070', 'rub', '1000', '500000', '[\"com.mifors.izhcombank\"]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\"]', 20, 10, 'logo_mklmbaj6bjggakxa9e0snw3ohbwk0e6d.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (113, 'Оранжевый Банк', 'bankorange', '100000000286', 'rub', '1000', '500000', '[]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"phone\",\"card\"]', 20, 15, 'logo_gbyshydbyoitlntmlncy3fhmrrxzxrou.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (114, 'Элплат', 'elpat', '100000000086', 'rub', '1000', '500000', '[]', NULL, 9, 7, 3, 10, 1, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 15, 'logo_eikmklxg3unrjccwx10rsugwtomuwfsr.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (115, 'Абхазия Универсал-банк', 'universalbank', '100055000165', 'rub', '1000', '500000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 20, 1, 'logo_sbreert6igxcbmn3qm3ecybhizl85rmr.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (116, 'ТрансСтройБанк', 'transstroybank', '100000000197', 'rub', '1000', '500000', '[\"com.intervale.sbp.atlas\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_cehskjsjcx0xykg8ejeiugsyngdxiw8n.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (117, 'Мир Привилегий Банк', 'mp-bank', '100000000169', 'rub', '1000', '500000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_awma3tvrw68dlckqiwa3eywvjnfbv6k9.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (118, 'Тест банк', 'test', '108807700001', 'rub', '100', '1000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"account_number\",\"phone\"]', 15, 1, 'logo_1r9awavshmhahc6zv7s7uykctsikdhqj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (119, 'Алмазэргиэнбанк', 'almazjergijenbank', '100000000080', 'rub', '1000', '500000', '[\"ru.albank.online.aebit\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 15, 1, 'logo_pycfbbe99tsjxypdinay1ni3kdiub67g.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (120, 'СибСоцБанк', 'sibsoc', '100000000166', 'rub', '1000', '500000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_4kegaeh1apc554medhgdt5ifjthme4jx.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (121, 'Авито', 'avito', '106600000111', 'rub', '1000', '30000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\"]', 10, 1, 'logo_v3uffi2kbdxvr2s6nffn0m9eqfbchbgb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (122, 'Развитие Столица', 'dcapital', '100000000172', 'rub', '1000', '500000', '[\"ru.ftc.faktura.razvitiestolica\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 15, 1, 'logo_hyhlpvd7xdpefc3zfzvmu3vi0kjdhy6i.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (123, 'Примсоцбанк', 'pskb', '100000000088', 'rub', '1000', '500000', '[\"ru.ftc.faktura.primsoc\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_f9ladkapm7lokhgloqdkc18ryvjttfh0.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (124, 'Банк Саратов', 'bank-saratov', '100000000126', 'rub', '1000', '500000', '[\"ru.ftc.faktura.banksaratov\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_el0nwll94zsnhlff2o23sqy5x6jmxerm.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (125, 'Таврический Банк', 'tavrich', '100000000173', 'rub', '1000', '500000', '[\"ru.ftc.faktura.tavrich\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_cllsorqofaoq00xflenqwndel5gytbnn.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (126, 'Тольяттихимбанк', 'thbank', '100000000152', 'rub', '1000', '500000', '[\"com.bifit.mobile.citizen.thbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_iulk1vyghsfl57osgvpyrsw8ug9pem23.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (127, 'АКБ Держава', 'akb-derzhava', '100000000235', 'rub', '1000', '500000', '[\"ru.ftc.faktura.derzhava\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_sarbfrobj70nvw9oaizfssgkwsh5wgg5.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (128, 'НБД-Банк', 'nbd-bank', '100000000134', 'rub', '1000', '500000', '[\"ru.nbd.android\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_xvq6dgcd4aogjfm6dxsjxptmvdhxnfjl.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (129, 'БАНК СНГБ', 'sngb', '100000000091', 'rub', '1000', '500000', '[\"ru.sngb.dbo.client.android\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_bvzh1mmdxfwkayk7yaeijv4af0mqead2.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (130, 'Енисейский объединенный банк', 'enisejskij', '100000000258', 'rub', '1000', '500000', '[\"ru.ftc.faktura.united\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_4ho7b1vrzbncatbfypyxnfa9ir9e8lbb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (131, 'Банк Венец', 'venets-bank', '100000000153', 'rub', '1000', '500000', '[\"ru.ftc.faktura.venetsbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_q2donf82amybyjwcbkda9kqk1h3bq2we.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (132, 'УралПромБанк', 'uralprombank', '100000000142', 'rub', '1000', '500000', '[\"ru.uralprombank.mobilebanknew.googleplay\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_ypw01up2zjina4xwldlgwrjugqdlhe6n.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (133, 'Банк Национальный стандарт', 'ns-bank', '100000000243', 'rub', '1000', '500000', '[\"ru.ftc.faktura.nsbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_i8aiz1edkjxxcjhm30owjndzseegfavr.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (134, 'Банк Екатеринбург', 'bank-ekaterinburg', '100000000090', 'rub', '1000', '500000', '[\"ru.emb.android\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_ckwblakgnrb8hox3pxuy8yqg8gzqkovi.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (135, 'Авто Финанс Банк', 'avtofinbank', '100000000253', 'rub', '1000', '500000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_a0xmfnazyk3vegf4aq2pdneo97jgoim0.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (136, 'Стройлесбанк', 'kb-strojlesbank', '100000000193', 'rub', '1000', '500000', '[\"com.bssys.stroylesretail\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_30vxxuxnhwysy0md1zvoulvodqi4b4dq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (137, 'Кузнецкбизнесбанк', 'kbb', '100000000195', 'rub', '1000', '500000', '[\"ru.ftc.faktura.kbb\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_pfagbuat14ozmi8qqjjxn0iqi51mxxh6.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (138, 'Нацинвестпромбанк', 'nipbank', '100000000185', 'rub', '1000', '500000', '[\"ru.ftc.faktura.nipbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_ui5rvzntb75yumzubjsudtn0nxkq3nvk.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (139, 'АКБ Алеф-Банк', 'alefbank', '100000000113', 'rub', '1000', '500000', '[\"ru.ftc.faktura.alefbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_ddc3ecvjexacrrziyytlzkiza7jdzlpt.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (140, 'Внешфинбанк', 'vfbank', '100000000248', 'rub', '1000', '500000', '[\"com.bifit.vfbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_tntqpvs88xaflwsjns3fk3v0ln1yxd9m.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (141, 'АресБанк', 'aresbank', '100000000129', 'rub', '1000', '500000', '[]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_ntgeeduxlho4ueiap712bypmpo3ekh28.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (142, 'Северный Народный Банк', 'sevnb', '100000000208', 'rub', '1000', '500000', '[\"com.snb.online\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_dshxodax6fvsa9kjhsmql26wwbblptzb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (143, 'Банк Объединенный капитал', 'okbank', '100000000182', 'rub', '1000', '500000', '[\"com.bifit.mobile.citizen.okbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_nxouuxs5wz3jx3cdffskelfbk1h9eqtr.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (144, 'Татсоцбанк', 'tatsotsbank', '100000000189', 'rub', '1000', '500000', '[\"com.tatsotsbank.dbomobile\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_7g7k9uacz796ucbqzf4dlewojvztyoyj.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (145, 'Земский банк', 'zemsky', '100000000066', 'rub', '1000', '500000', '[\"ru.ftc.faktura.zemskybank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\",\"account_number\"]', 10, 1, 'logo_vpqc0wlm7fvu34crd0d04tnspaiohmyn.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (146, 'Оренбург Банк', 'orbank', '100000000124', 'rub', '1000', '500000', '[\"ru.ftc.faktura.orbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_y5dpkrfztbhym6eifwbk5r7nxuchqseq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (147, 'Агропромкредит', 'apkbank', '100000000118', 'rub', '1000', '500000', '[\"ru.ftc.faktura.agropromkredit\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"account_number\",\"card\"]', 10, 1, 'logo_2tyi0caysctyjockyogbprgdtdmwanze.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (148, 'Банк Казани', 'bankofkazan', '100000000191', 'rub', '1000', '500000', '[\"com.isimplelab.ionic.kazan.fl\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_t0zgia7qov42zpumgvmkx6nm6tqfykjw.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (149, 'Социум Банк', 'socium-bank', '100000000223', 'rub', '1000', '500000', '[\"com.intervale.sbp.atlas\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_tsdu0mu6clso0clzzr5cyoosc2awesbi.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (150, 'Синко Банк', 'sinko-bank', '100000000148', 'rub', '1000', '500000', '[\"com.intervale.sbp.atlas\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_a1yhors7cgtdmssg8xf0clwauijj6q9a.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (151, 'ИШБАНК', 'ishbank', '100000000199', 'rub', '1000', '500000', '[\"com.bifit.pmobile.isbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_gmvzqe1wr6dx0gpsudkmopgnnamsizuv.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (152, 'Горбанк', 'gorbank', '100000000125', 'rub', '1000', '500000', '[\"com.isimplelab.ionic.gorbank.prod\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_eh6hzkjvf1eoi5mnaejuxhcnmgjycx6v.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (153, 'Москомбанк', 'moskombank', '100000000176', 'rub', '1000', '500000', '[\"ru.ftc.faktura.moscombank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_0wemvuqeyhrlqdhht3b9hewvdvh23ggu.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (154, 'Русьуниверсалбанк', 'rus-universalbank', '100000000165', 'rub', '1000', '500000', '[\"ru.rubank.ubsmobile\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_ulecnqfjdxvvrem66qmhkl2ti4oqwsjq.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (155, 'Пойдём Банк', 'poidem', '100000000103', 'rub', '1000', '500000', '[\"com.openwaygroup.ic.panda.poidem\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_orcng5hxe7r5mi7nom8y0xpxhm9axpxb.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (156, 'Белгородсоцбанк', 'ukb-belgorodsocbank', '100000000225', 'rub', '1000', '500000', '[\"com.bifit.mobile.citizen.belsocbank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_towhanptnlykw9qoi6yh8zeouvsdrs9q.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (157, 'Хайс Банк', 'hajs', '100000000272', 'rub', '1000', '500000', '[\"com.hicebank.android\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_t5dn5j5ijnt4x3okxlcaghismczf2ycz.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (158, 'Севергазбанк', 'severgazbank', '100000000219', 'rub', '1000', '500000', '[\"com.bpc.crossplatform_trading.bpc_trading\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_ictd7c4xpx2gdvjnkmcdjhylhqomozmz.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (159, 'НРБанк', 'nrb', '100000000184', 'rub', '1000', '500000', '[\"com.bifit.nrb\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_8hfbnqnmdpa7sjtunzfriz5caslijr4o.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (160, 'Москоммерцбанк', 'kb-moskommercbank', '100000000110', 'rub', '1000', '500000', '[\"com.bifit.mobile.citizen.moskb\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_clllogpldizufn2jfvsbfcbrsx5rjse3.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (161, 'Новобанк', 'novobank', '100000000222', 'rub', '1000', '500000', '[\"ru.ftc.faktura.novobank\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"card\",\"phone\"]', 10, 1, 'logo_zf0gjygpfjbiqroxje1afss7pksgcv1q.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (162, 'Банк Финсервис', 'bank-finservis', '100000000216', 'rub', '1000', '500000', '[\"com.finservice.mobile\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_sxucpujquqdrl7fnhrtzbdrvphdbepa2.png'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    (163, 'Новый век', 'novyj-vek', '100000000067', 'rub', '1000', '500000', '[\"com.isimplelab.ionic.standart\"]', NULL, 9, 7, 1, 10, 1, 1, 0, '[\"phone\",\"card\"]', 10, 1, 'logo_ameaoyqrnybwqhnbuuuhbjocpbaepp18.png');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
                           `id` bigint(20) UNSIGNED NOT NULL,
                           `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `detail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `detail_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `detail_initials` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `payout_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `base_liquidity_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `liquidity_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `service_commission_rate` float DEFAULT NULL,
                           `service_commission_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `trader_profit_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `trader_exchange_markup_rate` float DEFAULT NULL,
                           `trader_exchange_markup_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `base_exchange_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `exchange_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `sub_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `callback_url` longtext COLLATE utf8mb4_unicode_ci,
                           `payout_offer_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `payout_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `payment_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `sub_payment_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `trader_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `refuse_reason` longtext COLLATE utf8mb4_unicode_ci,
                           `cancel_reason` longtext COLLATE utf8mb4_unicode_ci,
                           `previous_trader_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `video_receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `finished_at` timestamp NULL DEFAULT NULL,
                           `expires_at` timestamp NULL DEFAULT NULL,
                           `created_at` timestamp NULL DEFAULT NULL,
                           `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payout_gateways`
--

CREATE TABLE `payout_gateways` (
                                   `id` bigint(20) UNSIGNED NOT NULL,
                                   `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `callback_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                   `enabled` tinyint(1) DEFAULT NULL,
                                   `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
                                   `created_at` timestamp NULL DEFAULT NULL,
                                   `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payout_offers`
--

CREATE TABLE `payout_offers` (
                                 `id` bigint(20) UNSIGNED NOT NULL,
                                 `max_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `min_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `detail_types` longtext COLLATE utf8mb4_unicode_ci,
                                 `active` tinyint(1) DEFAULT NULL,
                                 `occupied` tinyint(1) NOT NULL DEFAULT '0',
                                 `payment_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                                 `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
                                 `created_at` timestamp NULL DEFAULT NULL,
                                 `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
                               `id` bigint(20) UNSIGNED NOT NULL,
                               `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `created_at` timestamp NULL DEFAULT NULL,
                               `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
    (1, 'access admin panel', 'web', '2024-12-18 12:26:00', '2024-12-18 12:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
                                          `id` bigint(20) UNSIGNED NOT NULL,
                                          `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `tokenable_id` bigint(20) UNSIGNED NOT NULL,
                                          `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `abilities` text COLLATE utf8mb4_unicode_ci,
                                          `last_used_at` timestamp NULL DEFAULT NULL,
                                          `expires_at` timestamp NULL DEFAULT NULL,
                                          `created_at` timestamp NULL DEFAULT NULL,
                                          `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
                               `id` bigint(20) UNSIGNED NOT NULL,
                               `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `max_uses` int(11) NOT NULL DEFAULT '0' COMMENT '0 - unlimited',
                               `used_count` int(11) NOT NULL DEFAULT '0',
                               `is_active` tinyint(1) NOT NULL DEFAULT '1',
                               `team_leader_id` bigint(20) UNSIGNED NOT NULL,
                               `created_at` timestamp NULL DEFAULT NULL,
                               `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pulse_aggregates`
--

CREATE TABLE `pulse_aggregates` (
                                    `id` bigint(20) UNSIGNED NOT NULL,
                                    `bucket` int(10) UNSIGNED NOT NULL,
                                    `period` mediumint(8) UNSIGNED NOT NULL,
                                    `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `key` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `key_hash` binary(16) GENERATED ALWAYS AS (unhex(md5(`key`))) VIRTUAL,
                                    `aggregate` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `value` decimal(20,2) NOT NULL,
                                    `count` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pulse_entries`
--

CREATE TABLE `pulse_entries` (
                                 `id` bigint(20) UNSIGNED NOT NULL,
                                 `timestamp` int(10) UNSIGNED NOT NULL,
                                 `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `key` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `key_hash` binary(16) GENERATED ALWAYS AS (unhex(md5(`key`))) VIRTUAL,
                                 `value` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pulse_values`
--

CREATE TABLE `pulse_values` (
                                `id` bigint(20) UNSIGNED NOT NULL,
                                `timestamp` int(10) UNSIGNED NOT NULL,
                                `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `key` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                `key_hash` binary(16) GENERATED ALWAYS AS (unhex(md5(`key`))) VIRTUAL,
                                `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
                         `id` bigint(20) UNSIGNED NOT NULL,
                         `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `created_at` timestamp NULL DEFAULT NULL,
                         `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
                                                                                 (1, 'Super Admin', 'web', '2024-12-18 12:26:00', '2024-12-18 12:26:00'),
                                                                                 (2, 'Trader', 'web', '2024-12-18 12:26:00', '2024-12-18 12:26:00'),
                                                                                 (3, 'Merchant', 'web', '2024-12-18 12:26:00', '2024-12-18 12:26:00'),
                                                                                 (4, 'Support', 'web', '2025-03-28 19:33:41', '2025-03-28 19:33:41'),
                                                                                 (5, 'Team Leader', 'web', '2025-04-25 08:37:44', '2025-04-25 08:37:44'),
                                                                                 (6, 'Merchant Support', 'web', '2025-04-25 08:37:49', '2025-04-25 08:37:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
                                        `permission_id` bigint(20) UNSIGNED NOT NULL,
                                        `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
    (1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sender_stop_lists`
--

CREATE TABLE `sender_stop_lists` (
                                     `id` bigint(20) UNSIGNED NOT NULL,
                                     `sender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sender_stop_lists`
--

INSERT INTO `sender_stop_lists` (`id`, `sender`) VALUES
                                                     (2, 'com.free.tiptop.vpn.proxy'),
                                                     (3, 'com.whatsapp'),
                                                     (4, 'ru.vk.store'),
                                                     (6, 'com.zhiliaoapp.musically'),
                                                     (7, 'eu.livesport.flashscore_com'),
                                                     (9, 'com.zhiliaoapp.musically'),
                                                     (10, 'com.vkontakte.android'),
                                                     (12, 'android'),
                                                     (13, 'com.wildberries.ru'),
                                                     (14, 're.sova.five'),
                                                     (15, 'com.hihonor.systemmanager'),
                                                     (16, 'tv.twitch.android.app'),
                                                     (17, 'com.google.android.dialer'),
                                                     (18, '+74950327276'),
                                                     (21, 'com.samsung.android.honeyboard'),
                                                     (22, 'ru.dublgis.dgismobile'),
                                                     (23, 'ru.yandex.music'),
                                                     (24, 'ru.mail.mailapp'),
                                                     (25, 'stepcounter.pedometer.stepstracker'),
                                                     (26, 'com.taxsee.driver'),
                                                     (27, 'com.miui.securitycenter'),
                                                     (28, 'com.transsion.batterylab'),
                                                     (29, 'ru.ivi.client'),
                                                     (31, 'com.android.systemui'),
                                                     (32, 'com.xiaomi.mi_connect_service'),
                                                     (33, 'com.google.android.setupwizard'),
                                                     (34, 'com.sec.android.app.samsungapps'),
                                                     (35, 'org.amnezia.vpn'),
                                                     (36, 'com.android.providers.contacts'),
                                                     (37, 'com.freevpnplanet'),
                                                     (38, 'com.android.bluetooth'),
                                                     (39, 'com.samsung.android.app.smartcapture'),
                                                     (40, 'com.android.vending'),
                                                     (41, 'com.viber.voip'),
                                                     (42, 'com.pinterest'),
                                                     (43, 'com.sec.android.daemonapp'),
                                                     (44, 'com.sh.smart.caller'),
                                                     (45, 'com.nearme.romupdate'),
                                                     (46, 'com.avito.android'),
                                                     (47, 'app.source.getcontact'),
                                                     (48, 'ru.tander.magnit'),
                                                     (50, 'com.android.phone'),
                                                     (51, 'yota'),
                                                     (53, 'com.android.providers.downloads'),
                                                     (54, 'com.hihonor.contacts'),
                                                     (55, 'com.transsion.deskclock'),
                                                     (56, 'telegram.'),
                                                     (57, 'ru.starlinex.app'),
                                                     (58, 'net.bat.store'),
                                                     (59, 'com.microsoft.appmanager'),
                                                     (60, 'com.sec.android.app.shealth'),
                                                     (61, 'com.uma.musicvk'),
                                                     (62, 'com.instagram.android'),
                                                     (63, 'com.digibites.accubattery'),
                                                     (64, 'com.notvpn'),
                                                     (65, 'io.papervpn.android.client'),
                                                     (66, 'com.microsoft.office.officehubrow'),
                                                     (67, 'ru.kfc.kfc_delivery'),
                                                     (68, 'net.typeblog.socks'),
                                                     (69, 'com.bybit.app'),
                                                     (71, 'ch.protonvpn.android'),
                                                     (72, 'com.example.payeventhandler'),
                                                     (73, 'ru.yandex.taxi'),
                                                     (74, 'com.google.android.inputmethod.latin'),
                                                     (75, 'ru.yandex.taximeter'),
                                                     (76, 'dtac'),
                                                     (77, 'lime-zaim'),
                                                     (78, 'com.grus.callblocker'),
                                                     (79, 'com.android.contacts'),
                                                     (80, 'com.android.incallui'),
                                                     (81, 'com.bp.antifs.bridgepay'),
                                                     (82, '020553091'),
                                                     (83, 'com.supervpn.vpn.free.proxy'),
                                                     (84, 'com.vk.vkvideo'),
                                                     (86, 'com.android.deskclock'),
                                                     (87, 'com.android.settings'),
                                                     (88, 'zaschitnik'),
                                                     (89, 'com.android.chrome'),
                                                     (90, 'com.miui.gallery'),
                                                     (91, 'com.samsung.android.incallui'),
                                                     (92, 'com.huawei.systemmanager'),
                                                     (93, 'com.google.android.gms'),
                                                     (94, 'com.google.android.googlequicksearchbox'),
                                                     (95, 'com.adguard.vpn'),
                                                     (96, 'com.google.android.youtube'),
                                                     (97, 'ru.yandex.searchplugin'),
                                                     (98, 'com.yandex.searchapp'),
                                                     (99, 'com.yandex.browser'),
                                                     (100, 'com.miui.misound'),
                                                     (101, 'com.huami.watch.hmwatchmanager'),
                                                     (102, 'com.vk.love'),
                                                     (103, 'com.sec.android.app.clockpackage'),
                                                     (104, 'com.android.updater'),
                                                     (105, 'ru.burgerking'),
                                                     (106, 'com.lamoda.lite'),
                                                     (107, 'ru.dnevnik.tracker'),
                                                     (108, 'com.coloros.phonemanager'),
                                                     (109, 'com.sec.android.gallery3d'),
                                                     (110, 'com.tct.dialer'),
                                                     (111, 'com.android.browser'),
                                                     (112, 'mchs'),
                                                     (113, 'com.xiaomi.mipicks'),
                                                     (114, 'com.xiaomi.discover'),
                                                     (115, 'operator'),
                                                     (116, 'com.mediatek.simprocessor'),
                                                     (117, 'com.google.android.projection.gearhead'),
                                                     (118, 'com.google.android.deskclock'),
                                                     (119, 'com.miui.msa.global'),
                                                     (120, 'com.xiaomi.mipicks'),
                                                     (121, 'com.oppo.ota'),
                                                     (122, 'com.sprd.omacp'),
                                                     (123, 't2.ru'),
                                                     (124, 'com.xiaomi.simactivate.service'),
                                                     (125, 'com.xiaomi.xmsf'),
                                                     (126, 'com.xiaomi.simactivate.service'),
                                                     (127, 'com.xiaomi.simactivate.service'),
                                                     (128, 'com.mi.android.globalminusscreen'),
                                                     (129, 'com.xiaomi.xmsf'),
                                                     (130, 'mobiagentru'),
                                                     (131, 'com.xiaomi.simactivate.service'),
                                                     (132, 'com.mediatek.omacp'),
                                                     (133, '1ofd'),
                                                     (134, 'miranda'),
                                                     (135, 'mtc'),
                                                     (136, 'free.vpn.proxy.secure'),
                                                     (137, 'com.taxsee.taxsee'),
                                                     (138, 'ru.yandex.yandexnavi'),
                                                     (139, 'ru.euphoria.moozza.new'),
                                                     (140, 'ru.yandex.uber'),
                                                     (141, 'com.vlessoffvpn.appl'),
                                                     (142, 'app.kids360.kid'),
                                                     (143, 'com.radolyn.ayugram'),
                                                     (144, 'com.google.android.apps.wellbeing'),
                                                     (145, 'com.radolyn.ayugram'),
                                                     (146, 'com.samsung.android.lool'),
                                                     (147, 'com.android.server.telecom'),
                                                     (148, 'ru.rutube.app'),
                                                     (149, 'com.coloros.alarmclock'),
                                                     (150, 'pro.huobi'),
                                                     (151, 'com.vk.im'),
                                                     (152, 'beeline'),
                                                     (153, 'com.binance.dev'),
                                                     (154, 'by.baranovdev.brabus'),
                                                     (155, 'ru.start.androidmobile'),
                                                     (156, 'com.xiaomi.glgm'),
                                                     (157, 'samokat'),
                                                     (158, 'com.heytap.market'),
                                                     (159, 'com.grif.vmp'),
                                                     (160, 'com.perm.kate_new_6'),
                                                     (161, 'org.telegram.messenger'),
                                                     (162, '6505551212');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `user_agent` text COLLATE utf8mb4_unicode_ci,
                            `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                            `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
                            `id` bigint(20) UNSIGNED NOT NULL,
                            `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `value` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
                                                  (1, 'prime_time_bonus_starts', '02:00'),
                                                  (2, 'prime_time_bonus_ends', '06:00'),
                                                  (3, 'prime_time_bonus_rate', '0.5'),
                                                  (4, 'support_link', NULL),
                                                  (5, 'currency_price_parser_settings', '{\"rub\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":15},\"kzt\":{\"amount\":50000,\"payment_method\":549,\"ad_quantity\":3},\"byn\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"eur\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"tjs\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"kgs\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"uah\":{\"amount\":200,\"payment_method\":1,\"ad_quantity\":3},\"usd\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"azn\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"thb\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"try\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"idr\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3},\"aed\":{\"amount\":null,\"payment_method\":null,\"ad_quantity\":3}}'),
                                                  (6, 'prime_time_bonus_starts', '02:00'),
                                                  (7, 'prime_time_bonus_ends', '06:00'),
                                                  (8, 'funds_on_hold_time', '1440'),
                                                  (9, 'prime_time_bonus_starts', '02:00'),
                                                  (10, 'prime_time_bonus_ends', '06:00'),
                                                  (11, 'prime_time_bonus_rate', '0.5'),
                                                  (12, 'max_pending_disputes', '5'),
                                                  (13, 'prime_time_bonus_starts', '02:00'),
                                                  (14, 'prime_time_bonus_ends', '06:00'),
                                                  (15, 'prime_time_bonus_rate', '0.5'),
                                                  (16, 'max_rejected_disputes', '{\"count\":10,\"period\":600}'),
                                                  (17, 'max_rejected_disputes', '{\"count\":10,\"period\":600}'),
                                                  (18, 'deposit_link', 'https://example.com'),
                                                  (19, 'deposit_link', 'https://example.com/pay.php'),
                                                  (20, 'max_rejected_disputes', '{\"count\":10,\"period\":60}'),
                                                  (21, 'prime_time_bonus_ends', '06:00'),
                                                  (22, 'prime_time_bonus_rate', '0.5');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
                            `id` bigint(20) UNSIGNED NOT NULL,
                            `sender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `message` longtext COLLATE utf8mb4_unicode_ci,
                            `parsing_result` longtext COLLATE utf8mb4_unicode_ci,
                            `timestamp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `user_device_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `order_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `created_at` timestamp NULL DEFAULT NULL,
                            `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_parsers`
--

CREATE TABLE `sms_parsers` (
                               `id` bigint(20) UNSIGNED NOT NULL,
                               `payment_gateway_id` bigint(20) UNSIGNED DEFAULT NULL,
                               `format` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                               `regex` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_stop_words`
--

CREATE TABLE `sms_stop_words` (
                                  `id` bigint(20) UNSIGNED NOT NULL,
                                  `word` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_stop_words`
--

INSERT INTO `sms_stop_words` (`id`, `word`) VALUES
                                                (1, 'поступил платёж'),
                                                (2, 'отказ'),
                                                (3, 'otkaz'),
                                                (4, 'отклонено'),
                                                (5, 'отклонена'),
                                                (6, 'заблокирован'),
                                                (7, 'заблокирована');

-- --------------------------------------------------------

--
-- Table structure for table `telegrams`
--

CREATE TABLE `telegrams` (
                             `id` bigint(20) UNSIGNED NOT NULL,
                             `telegram_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `member_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telescope_entries`
--

CREATE TABLE `telescope_entries` (
                                     `sequence` bigint(20) UNSIGNED NOT NULL,
                                     `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `family_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                     `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
                                     `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telescope_entries_tags`
--

CREATE TABLE `telescope_entries_tags` (
                                          `entry_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telescope_monitoring`
--

CREATE TABLE `telescope_monitoring` (
                                        `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
                                `id` bigint(20) UNSIGNED NOT NULL,
                                `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `direction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `balance_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
                                `created_at` timestamp NULL DEFAULT NULL,
                                `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
                         `id` bigint(20) UNSIGNED NOT NULL,
                         `merchant_id` bigint(20) UNSIGNED DEFAULT NULL,
                         `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `email_verified_at` timestamp NULL DEFAULT NULL,
                         `avatar_uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `avatar_style` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `apk_access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `api_access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `google2fa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `is_online` tinyint(1) NOT NULL DEFAULT '0',
                         `is_payout_online` tinyint(1) NOT NULL DEFAULT '0',
                         `is_vip` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'VIP статус пользователя',
                         `referral_commission_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
                         `payouts_enabled` tinyint(1) NOT NULL DEFAULT '0',
                         `stop_traffic` tinyint(1) NOT NULL DEFAULT '0',
                         `traffic_enabled_at` timestamp NULL DEFAULT NULL,
                         `banned_at` timestamp NULL DEFAULT NULL,
                         `created_at` timestamp NULL DEFAULT NULL,
                         `updated_at` timestamp NULL DEFAULT NULL,
                         `promo_code_id` bigint(20) UNSIGNED DEFAULT NULL,
                         `promo_used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `merchant_id`, `name`, `email`, `email_verified_at`, `avatar_uuid`, `avatar_style`, `password`, `apk_access_token`, `api_access_token`, `remember_token`, `google2fa_secret`, `is_online`, `is_payout_online`, `is_vip`, `referral_commission_percentage`, `payouts_enabled`, `stop_traffic`, `traffic_enabled_at`, `banned_at`, `created_at`, `updated_at`, `promo_code_id`, `promo_used_at`) VALUES
    (1, NULL, 'admin', 'administrator', NULL, 'e876c200-3813-4eea-8e9f-98380feecd94', 'miniavs', '$2y$12$lUGM4hPx1k0Ia9HwAabinedwYD4RIx3GvkuTLzahdhNOKF/L8AVX6', '73rnfs1chuci6mrvevnpeeom66rdqel1', 'qzhmtloiexeezcjjmsrmhd6qapc5uhfj', 'LreN0iu5sFYLHqq2JbUaxiAKAX69F3MBkHyLwgxGjx0f3ZWfJPBlugykL2wG', '', 0, 0, 0, '0.00', 0, 0, '2025-03-24 06:12:53', NULL, '2024-12-18 12:26:00', '2025-10-08 01:00:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
                                `id` bigint(20) UNSIGNED NOT NULL,
                                `user_id` bigint(20) UNSIGNED NOT NULL,
                                `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название устройства',
                                `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Токен для доступа к API',
                                `android_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Android ID устройства',
                                `device_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Модель устройства',
                                `android_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Версия Android',
                                `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Производитель устройства',
                                `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Бренд устройства',
                                `connected_at` timestamp NULL DEFAULT NULL COMMENT 'Дата подключения устройства',
                                `created_at` timestamp NULL DEFAULT NULL,
                                `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_device_pings`
--

CREATE TABLE `user_device_pings` (
                                     `id` bigint(20) UNSIGNED NOT NULL,
                                     `user_device_id` bigint(20) UNSIGNED NOT NULL,
                                     `bucket_5s` bigint(20) UNSIGNED NOT NULL,
                                     `created_at` timestamp NULL DEFAULT NULL,
                                     `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_login_histories`
--

CREATE TABLE `user_login_histories` (
                                        `id` bigint(20) UNSIGNED NOT NULL,
                                        `user_id` bigint(20) UNSIGNED NOT NULL,
                                        `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `device_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `operating_system` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `is_successful` tinyint(1) NOT NULL DEFAULT '1',
                                        `created_at` timestamp NULL DEFAULT NULL,
                                        `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_metas`
--

CREATE TABLE `user_metas` (
                              `id` bigint(20) UNSIGNED NOT NULL,
                              `allowed_markets` longtext COLLATE utf8mb4_unicode_ci,
                              `allowed_categories` json DEFAULT NULL,
                              `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_metas`
--

INSERT INTO `user_metas` (`id`, `allowed_markets`, `allowed_categories`, `user_id`) VALUES
    (1, '[]', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_notes`
--

CREATE TABLE `user_notes` (
                              `id` bigint(20) UNSIGNED NOT NULL,
                              `user_id` bigint(20) UNSIGNED NOT NULL,
                              `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
                              `created_by` bigint(20) UNSIGNED NOT NULL,
                              `created_at` timestamp NULL DEFAULT NULL,
                              `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
                           `id` bigint(20) UNSIGNED NOT NULL,
                           `merchant_balance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `trust_balance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `reserve_balance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `commission_balance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `teamleader_balance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                           `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                           `created_at` timestamp NULL DEFAULT NULL,
                           `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `merchant_balance`, `trust_balance`, `reserve_balance`, `commission_balance`, `teamleader_balance`, `currency`, `user_id`, `created_at`, `updated_at`) VALUES
    (1, '0', '0', '0', '0', '0', NULL, 1, '2024-12-18 12:26:00', '2025-10-07 07:43:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
    ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
    ADD PRIMARY KEY (`key`);

--
-- Indexes for table `callback_logs`
--
ALTER TABLE `callback_logs`
    ADD PRIMARY KEY (`id`),
  ADD KEY `callback_logs_callbackable_type_callbackable_id_index` (`callbackable_type`,`callbackable_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `category_merchant`
--
ALTER TABLE `category_merchant`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_merchant_category_id_merchant_id_unique` (`category_id`,`merchant_id`),
  ADD KEY `category_merchant_merchant_id_foreign` (`merchant_id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_disputes_status` (`status`),
  ADD KEY `idx_disputes_trader_id` (`trader_id`),
  ADD KEY `idx_disputes_order_id` (`order_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `funds_on_holds`
--
ALTER TABLE `funds_on_holds`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
    ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `merchants`
--
ALTER TABLE `merchants`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `merchant_api_request_logs`
--
ALTER TABLE `merchant_api_request_logs`
    ADD PRIMARY KEY (`id`),
  ADD KEY `merchant_api_request_logs_merchant_id_index` (`merchant_id`),
  ADD KEY `merchant_api_request_logs_order_id_index` (`order_id`),
  ADD KEY `merchant_api_request_logs_external_id_index` (`external_id`),
  ADD KEY `merchant_api_request_logs_is_successful_index` (`is_successful`),
  ADD KEY `merchant_api_request_logs_created_at_index` (`created_at`),
  ADD KEY `merchant_api_request_logs_request_id_index` (`request_id`);

--
-- Indexes for table `merchant_api_statistics`
--
ALTER TABLE `merchant_api_statistics`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `merchant_api_statistics_date_is_successful_currency_unique` (`date`,`is_successful`,`currency`),
  ADD KEY `merchant_api_statistics_date_index` (`date`),
  ADD KEY `merchant_api_statistics_is_successful_index` (`is_successful`),
  ADD KEY `merchant_api_statistics_currency_index` (`currency`);

--
-- Indexes for table `merchant_supports`
--
ALTER TABLE `merchant_supports`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `merchant_supports_merchant_id_support_id_unique` (`merchant_id`,`support_id`),
  ADD KEY `merchant_supports_support_id_foreign` (`support_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
    ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
    ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_uuid` (`uuid`),
  ADD KEY `idx_orders_uuid` (`uuid`),
  ADD KEY `idx_orders_external_id` (`external_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_merchant_status` (`merchant_id`,`status`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_expires_at` (`expires_at`),
  ADD KEY `idx_orders_payment_gateway_id` (`payment_gateway_id`),
  ADD KEY `idx_orders_payment_detail_id` (`payment_detail_id`),
  ADD KEY `idx_orders_finished_at` (`finished_at`),
  ADD KEY `idx_orders_merchant_id` (`merchant_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
    ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_details_user_id` (`user_id`),
  ADD KEY `idx_payment_details_is_active` (`is_active`),
  ADD KEY `idx_payment_details_currency` (`currency`),
  ADD KEY `idx_payment_details_user_active` (`user_id`,`is_active`),
  ADD KEY `idx_payment_details_detail_type` (`detail_type`),
  ADD KEY `idx_payment_details_daily_limit` (`daily_limit`),
  ADD KEY `idx_payment_details_current_daily_limit` (`current_daily_limit`),
  ADD KEY `idx_payment_details_max_pending_orders_quantity` (`max_pending_orders_quantity`),
  ADD KEY `idx_payment_details_user_device_id` (`user_device_id`),
  ADD KEY `idx_payment_details_last_used_at` (`last_used_at`),
  ADD KEY `idx_payment_details_archived_at` (`archived_at`),
  ADD KEY `payment_details_min_order_amount_index` (`min_order_amount`),
  ADD KEY `payment_details_max_order_amount_index` (`max_order_amount`);

--
-- Indexes for table `payment_detail_payment_gateway`
--
ALTER TABLE `payment_detail_payment_gateway`
    ADD PRIMARY KEY (`id`),
  ADD KEY `payment_detail_payment_gateway_payment_detail_id_foreign` (`payment_detail_id`),
  ADD KEY `payment_detail_payment_gateway_payment_gateway_id_foreign` (`payment_gateway_id`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_gateways_code` (`code`),
  ADD KEY `idx_payment_gateways_currency` (`currency`),
  ADD KEY `idx_payment_gateways_is_active` (`is_active`),
  ADD KEY `idx_payment_gateways_currency_active` (`currency`,`is_active`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payout_gateways`
--
ALTER TABLE `payout_gateways`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payout_offers`
--
ALTER TABLE `payout_offers`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promo_codes_code_unique` (`code`),
  ADD KEY `promo_codes_team_leader_id_foreign` (`team_leader_id`);

--
-- Indexes for table `pulse_aggregates`
--
ALTER TABLE `pulse_aggregates`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pulse_aggregates_bucket_period_type_aggregate_key_hash_unique` (`bucket`,`period`,`type`,`aggregate`,`key_hash`),
  ADD KEY `pulse_aggregates_period_bucket_index` (`period`,`bucket`),
  ADD KEY `pulse_aggregates_type_index` (`type`),
  ADD KEY `pulse_aggregates_period_type_aggregate_bucket_index` (`period`,`type`,`aggregate`,`bucket`);

--
-- Indexes for table `pulse_entries`
--
ALTER TABLE `pulse_entries`
    ADD PRIMARY KEY (`id`),
  ADD KEY `pulse_entries_timestamp_index` (`timestamp`),
  ADD KEY `pulse_entries_type_index` (`type`),
  ADD KEY `pulse_entries_key_hash_index` (`key_hash`),
  ADD KEY `pulse_entries_timestamp_type_key_hash_value_index` (`timestamp`,`type`,`key_hash`,`value`);

--
-- Indexes for table `pulse_values`
--
ALTER TABLE `pulse_values`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pulse_values_type_key_hash_unique` (`type`,`key_hash`),
  ADD KEY `pulse_values_timestamp_index` (`timestamp`),
  ADD KEY `pulse_values_type_index` (`type`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
    ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sender_stop_lists`
--
ALTER TABLE `sender_stop_lists`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
    ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_parsers`
--
ALTER TABLE `sms_parsers`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_stop_words`
--
ALTER TABLE `sms_stop_words`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `telegrams`
--
ALTER TABLE `telegrams`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `telescope_entries`
--
ALTER TABLE `telescope_entries`
    ADD PRIMARY KEY (`sequence`),
  ADD UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
  ADD KEY `telescope_entries_batch_id_index` (`batch_id`),
  ADD KEY `telescope_entries_family_hash_index` (`family_hash`),
  ADD KEY `telescope_entries_created_at_index` (`created_at`),
  ADD KEY `telescope_entries_type_should_display_on_index_index` (`type`,`should_display_on_index`);

--
-- Indexes for table `telescope_entries_tags`
--
ALTER TABLE `telescope_entries_tags`
    ADD PRIMARY KEY (`entry_uuid`,`tag`),
  ADD KEY `telescope_entries_tags_tag_index` (`tag`);

--
-- Indexes for table `telescope_monitoring`
--
ALTER TABLE `telescope_monitoring`
    ADD PRIMARY KEY (`tag`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_apk_access_token_unique` (`apk_access_token`),
  ADD UNIQUE KEY `users_api_access_token_unique` (`api_access_token`),
  ADD KEY `idx_users_banned_at` (`banned_at`),
  ADD KEY `idx_users_created_at` (`created_at`),
  ADD KEY `idx_users_is_online` (`is_online`),
  ADD KEY `users_promo_code_id_foreign` (`promo_code_id`),
  ADD KEY `idx_users_stop_traffic` (`stop_traffic`),
  ADD KEY `users_merchant_id_foreign` (`merchant_id`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_devices_token_unique` (`token`),
  ADD KEY `user_devices_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_device_pings`
--
ALTER TABLE `user_device_pings`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `udp_device_bucket_unique` (`user_device_id`,`bucket_5s`),
  ADD KEY `user_device_pings_user_device_id_created_at_index` (`user_device_id`,`created_at`);

--
-- Indexes for table `user_login_histories`
--
ALTER TABLE `user_login_histories`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_login_histories_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_metas`
--
ALTER TABLE `user_metas`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_notes`
--
ALTER TABLE `user_notes`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_notes_user_id_foreign` (`user_id`),
  ADD KEY `user_notes_created_by_foreign` (`created_by`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallets_merchant_balance` (`merchant_balance`),
  ADD KEY `idx_wallets_trust_balance` (`trust_balance`),
  ADD KEY `idx_wallets_reserve_balance` (`reserve_balance`),
  ADD KEY `idx_wallets_commission_balance` (`commission_balance`),
  ADD KEY `idx_wallets_user_id` (`user_id`),
  ADD KEY `idx_wallets_teamleader_balance` (`teamleader_balance`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `callback_logs`
--
ALTER TABLE `callback_logs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_merchant`
--
ALTER TABLE `category_merchant`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `funds_on_holds`
--
ALTER TABLE `funds_on_holds`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merchants`
--
ALTER TABLE `merchants`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merchant_api_request_logs`
--
ALTER TABLE `merchant_api_request_logs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merchant_api_statistics`
--
ALTER TABLE `merchant_api_statistics`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merchant_supports`
--
ALTER TABLE `merchant_supports`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_detail_payment_gateway`
--
ALTER TABLE `payment_detail_payment_gateway`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payout_gateways`
--
ALTER TABLE `payout_gateways`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payout_offers`
--
ALTER TABLE `payout_offers`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pulse_aggregates`
--
ALTER TABLE `pulse_aggregates`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pulse_entries`
--
ALTER TABLE `pulse_entries`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pulse_values`
--
ALTER TABLE `pulse_values`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sender_stop_lists`
--
ALTER TABLE `sender_stop_lists`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_parsers`
--
ALTER TABLE `sms_parsers`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_stop_words`
--
ALTER TABLE `sms_stop_words`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `telegrams`
--
ALTER TABLE `telegrams`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `telescope_entries`
--
ALTER TABLE `telescope_entries`
    MODIFY `sequence` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_device_pings`
--
ALTER TABLE `user_device_pings`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_login_histories`
--
ALTER TABLE `user_login_histories`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_metas`
--
ALTER TABLE `user_metas`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_notes`
--
ALTER TABLE `user_notes`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `category_merchant`
--
ALTER TABLE `category_merchant`
    ADD CONSTRAINT `category_merchant_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_merchant_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `merchant_api_request_logs`
--
ALTER TABLE `merchant_api_request_logs`
    ADD CONSTRAINT `merchant_api_request_logs_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`),
  ADD CONSTRAINT `merchant_api_request_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `merchant_supports`
--
ALTER TABLE `merchant_supports`
    ADD CONSTRAINT `merchant_supports_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `merchant_supports_support_id_foreign` FOREIGN KEY (`support_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
    ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
    ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
    ADD CONSTRAINT `fk_orders_merchant_id` FOREIGN KEY (`merchant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_payment_detail_id` FOREIGN KEY (`payment_detail_id`) REFERENCES `payment_details` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_payment_gateway_id` FOREIGN KEY (`payment_gateway_id`) REFERENCES `payment_gateways` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
    ADD CONSTRAINT `fk_payment_details_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_detail_payment_gateway`
--
ALTER TABLE `payment_detail_payment_gateway`
    ADD CONSTRAINT `payment_detail_payment_gateway_payment_detail_id_foreign` FOREIGN KEY (`payment_detail_id`) REFERENCES `payment_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_detail_payment_gateway_payment_gateway_id_foreign` FOREIGN KEY (`payment_gateway_id`) REFERENCES `payment_gateways` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promo_codes`
--
ALTER TABLE `promo_codes`
    ADD CONSTRAINT `promo_codes_team_leader_id_foreign` FOREIGN KEY (`team_leader_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
    ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `telescope_entries_tags`
--
ALTER TABLE `telescope_entries_tags`
    ADD CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
    ADD CONSTRAINT `users_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_promo_code_id_foreign` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
    ADD CONSTRAINT `user_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_device_pings`
--
ALTER TABLE `user_device_pings`
    ADD CONSTRAINT `user_device_pings_user_device_id_foreign` FOREIGN KEY (`user_device_id`) REFERENCES `user_devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_login_histories`
--
ALTER TABLE `user_login_histories`
    ADD CONSTRAINT `user_login_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notes`
--
ALTER TABLE `user_notes`
    ADD CONSTRAINT `user_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
